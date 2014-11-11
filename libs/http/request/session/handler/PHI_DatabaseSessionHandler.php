<?php
/**
 * HTTP セッションをデータベースで管理します。
 *
 * <i>セッションハンドラを有効にするには、あらかじめ "phi install-database-session" コマンドを実行し、データベースにセッションテーブルを作成>しておく必要があります。</i>
 * <i>現在のところ、MySQL 以外のデータベースドライバは動作をサポートしていません。</i>
 *
 * application.yml の設定例:
 * <code>
 * session:
 *   # セッションハンドラ
 *   handler:
 *     # セッションハンドラのクラス名 (固定)
 *     class: PHI_DatabaseSessionHandler
 *
 *     # セッションテーブルが配置されたデータソース ('database' 属性を参照)
 *     dataSource: default
 * </code>
 *
 * @package http.request.session.handler
 */
class PHI_DatabaseSessionHandler extends PHI_Object
{
  /**
   * @var PHI_DatabaseManager
   */
  private $_database;

  /**
   * @var string
   */
  private $_dataSourceId;

  /**
   * @var PHI_DatabaseConnection
   */
  private $_connection;

  /**
   * コンストラクタ。
   *
   * @param PHI_ParameterHolder $holder application.yml に定義されたハンドラ属性。
   */
  private function __construct(PHI_ParameterHolder $config)
  {
    $this->_database = PHI_DatabaseManager::getInstance();
    $this->_dataSourceId = $config->get('dataSource', PHI_DatabaseManager::DEFAULT_DATASOURCE_ID);

    session_set_save_handler(
      array($this, 'open'),
      array($this, 'close'),
      array($this, 'read'),
      array($this, 'write'),
      array($this, 'destroy'),
      array($this, 'gc')
    );
  }

  /**
   * セッション管理をデータベースにハンドリングします。
   *
   * @param PHI_ParameterHolder $config セッションハンドラ属性。
   * @return PHI_DatabaseSessionHandler PHI_DatabaseSessionHandler のインスタンスを返します。
   */
  public static function handler(PHI_ParameterHolder $config)
  {
    return new PHI_DatabaseSessionHandler($config);
  }

  /**
   * セッションストレージへの接続を行います。
   *
   * @param string $savePath セッションの保存パス。
   * @param string $sessionName セッション名。
   * @return bool セッションストレージへの接続に成功した場合は TRUE、失敗した場合は FALSE を返します。
   * @throws PHI_UnsupportedException ドライバがサポートされていない場合に発生。
   */
  public function open($savePath, $sessionName)
  {
    $this->_database->getProfiler()->stop();
    $this->_connection = $this->_database->getConnection($this->_dataSourceId);
    $driverName = $this->_connection->getDriverName();

    if ($driverName !== 'mysql') {
      $message = sprintf('Driver is not supported. [%s]', $driverName);
      throw new PHI_UnsupportedException($message);
    }

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
    $result = FALSE;

    if ($this->_database) {
      $this->_database->getProfiler()->start();
      $result = TRUE;
    }

    return $result;
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

    if ($this->_connection) {
      $query = 'SELECT session_data '
        .'FROM phi_sessions '
        .'WHERE session_id = :session_id';

      $stmt = $this->_connection->createStatement($query);
      $stmt->bindParam(':session_id', $sessionId);
      $resultSet = $stmt->executeQuery();

      if ($sessionData = $resultSet->readField(0)) {
        $result = $sessionData;
      }
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
    $result = FALSE;

    if ($this->_connection) {
      $query = 'REPLACE INTO phi_sessions(session_id, session_data, register_date, update_date) '
        .'VALUES(:session_id, :session_data, NOW(), NOW())';

      $stmt = $this->_connection->createStatement($query);
      $stmt->bindParam(':session_data', $sessionData);
      $stmt->bindParam(':session_id', $sessionId);
      $affectedRows = $stmt->execute();

      if ($affectedRows) {
        $result = TRUE;
      }
    }

    return $result;
  }

  /**
   * セッションを破棄します。
   *
   * @param string $sessionId セッション ID。
   * @return bool セッションの破棄に成功した場合は TRUE、失敗した場合は FALSE を返します。
   */
  public function destroy($sessionId)
  {
    $result = TRUE;

    if ($this->_connection) {
      $query = 'DELETE FROM phi_sessions '
        .'WHERE session_id = :session_id';

      $pstmt = $this->_connection->createStatement($query);
      $pstmt->bindParam(':session_id', $sessionId);
      $pstmt->execute();

      $result = TRUE;
    }

    return $result;
  }

  /**
   * ガベージコレクタを起動します。
   *
   * @param int $lifetime セッションの生存期間。単位は秒。
   * @return bool ガベージコレクタの起動に成功した場合は TRUE、失敗した場合は FALSE を返します。
   */
  public function gc($lifetime)
  {
    $result = FALSE;

    if ($this->_connection) {
      $query = 'DELETE FROM phi_sessions '
        .'WHERE update_date < NOW() + \'- :lifetime secs\'';

      $pstmt = $this->_connection->createStatement($query);
      $pstmt->bindParam(':lifetime', $lifetime);
      $pstmt->execute();

      $result = TRUE;
    }

    return $result;
  }
}
