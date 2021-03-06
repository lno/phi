<?php
/**
 * HTTP セッションを APC (Alternative PHP Cache) で管理します。
 *
 * セッションハンドラを有効にするには、実行環境に {@link http://www.php.net/manual/apc.installation.php APC モジュール} を組み込む必要があります。
 *
 * <i>このハンドラはセッションデータを読み書きする際の排他制御 (ロック) をサポートしていません。
 * データの整合性を保証するには、アプリケーションサイドでロック機構を実装する必要があります。
 *
 * 例えばページ A を開く際にセッションデータ B が必要とします。
 * ユーザがデータ B を持つ場合 (セッションデータをチェック) は 1〜10 秒かかるプロセス C を実行後、データベースにレコード D を作成してセッションからデータ B を削除します。
 * ここで排他制御に詳しくないプログラマは、例えユーザが複数同時に A ページを開いたとしても、D レコードが複数作成されることはないと予想するかもしれません。
 * しかし実際は、後に開いたページの処理 (プロセス C) が先に開いたページより速く終わることで、複数のレコードが登録されることになります。</i>
 *
 * @package http.request.session.handler
 */
class PHI_APCSessionHandler extends PHI_Object
{
  /**
   * セッションの生存期間。
   * @var int
   */
  private $_lifetime;

  /**
   * コンストラクタ。
   *
   * @param PHI_ParameterHolder $holder application.yml に定義されたハンドラ属性。
   */
  private function __construct(PHI_ParameterHolder $config)
  {
    $this->_lifetime = ini_get('session.gc_maxlifetime');

    session_set_save_handler(array($this, 'open'),
      array($this, 'close'),
      array($this, 'read'),
      array($this, 'write'),
      array($this, 'destroy'),
      array($this, 'gc'));
  }

  /**
   * セッション管理を APC にハンドリングします。
   *
   * @param PHI_ParameterHolder $config セッションハンドラ属性。
   * @return PHI_APCSessionHandler PHI_APCSessionHandler のインスタンスを返します。
   */
  public static function handler(PHI_ParameterHolder $config)
  {
    return new PHI_APCSessionHandler($config);
  }

  /**
   * セッションストレージへの接続を行います。
   *
   * @param string $savePath セッションの保存パス。
   * @param string $sessionName セッション名。
   * @return bool セッションストレージへの接続に成功した場合は TRUE、失敗した場合は FALSE を返します。
   */
  public function open($savePath, $sessionName)
  {
    return TRUE;
  }

  /**
   * セッションストレージへの接続を閉じます。
   * このメソッドはセッション操作が終了する際に実行されます。
   *
   * @return bool セッションが正常に閉じられた場合は TRUE、失敗した場合は FALSE を返します。
   */
  public function close()
  {
    return TRUE;
  }

  /**
   * セッションに格納されている値を取得します。
   *
   * @param string $sessionId セッション ID。
   * @return string セッションに格納されている値を返します。値が存在しない場合は空文字を返します。
   */
  public function read($sessionId)
  {
    $result = '';
    $value = apc_fetch($sessionId);

    if ($value !== FALSE) {
      $result = $value;
    }

    return $result;
  }

  /**
   * セッションにデータを書き込みます。
   *
   * @param string $sessionId セッション ID。
   * @param mixed $sessionData 書き込むデータ。
   * @return bool 書き込みが成功した場合は TRUE、失敗した場合は FALSE を返します。
   */
  public function write($sessionId, $sessionData)
  {
    return apc_store($sessionId, $sessionData, $this->_lifetime);
  }

  /**
   * セッションを破棄します。
   *
   * @param string $sessionId セッション ID。
   * @return bool セッションの破棄に成功した場合は TRUE、失敗した場合は FALSE を返します。
   */
  public function destroy($sessionId)
  {
    return apc_delete($sessionId);
  }

  /**
   * ガベージコレクタを起動します。
   *
   * @param int $lifetime セッションの生存期間。単位は秒。
   * @return bool ガベージコレクタの起動に成功した場合は TRUE、失敗した場合は FALSE を返します。
   */
  public function gc($lifetime)
  {
    return TRUE;
  }
}
