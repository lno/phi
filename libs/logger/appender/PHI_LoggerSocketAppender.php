<?php
/**
 * ロギングしたメッセージをシリアライズした状態でソケットへ送信します。
 *
 * application.yml の設定例:
 * <code>
 * logger:
 *   # ログアペンダ ID。
 *   {appender_name}:
 *     # ロガークラス名。
 *     class: PHI_LoggerSocketAppender
 *
 *     # 接続先ホスト名。
 *     host: localhost
 *
 *     # 接続先ポート番号。
 *     port: 1980
 *
 *     # 接続タイムアウト秒。
 *     timeout: 30
 * </code>
 * <i>その他に指定可能な属性は {@link PHI_LoggerAppender} クラスを参照して下さい。</i>
 *
 * @package logger.appender
 */
class PHI_LoggerSocketAppender extends PHI_LoggerAppender
{
  /**
   * ソケットオブジェクトを取得します。
   *
   * @return PHI_Socket PHI_Socket のインスタンスを返します。
   */
  protected function getSocket()
  {
    static $socket;

    if ($socket === NULL) {
      $host = $this->holder->getString('host', 'localhost');
      $port = $this->holder->getInt('port', 1980);
      $timeout = $this->holder->getInt('timeout', 30);

      $socket = new PHI_Socket();
      $socket->connect($host, $port, $timeout);
    }

    return $socket;
  }

  /**
   * @see PHI_LoggerAppender::write()
   */
  public function write($className, $type, $message)
  {
    $message = $this->getLogFormat($className, $type, $message) . "\n";
    $message = serialize($message);

    $this->getSocket()->write($message);
  }

  /**
   * オブジェクトの破棄を行います。
   */
  public function __destruct()
  {
    $this->getSocket()->close();
  }
}
