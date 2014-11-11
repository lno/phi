<?php
/**
 * ロギングしたメッセージを標準出力に表示します。
 *
 * application.yml の設定例:
 * <code>
 * logger:
 *   # ログアペンダ ID。
 *   {appender_name}:
 *     # ロガークラス名。
 *     class: PHI_LoggerConsoleAppender
 *
 *     # 出力タイプの指定
 *     #   - stdout: 標準出力
 *     #   - stderr: 標準エラー
 *     type: stdout
 * </code>
 * <i>その他に指定可能な属性は {@link PHI_LoggerAppender} クラスを参照して下さい。</i>
 *
 * @package logger.appender
 */
class PHI_LoggerConsoleAppender extends PHI_LoggerAppender
{
  /**
   * @var string
   */
  private $_type;

  /**
   * @see PHI_LoggerAppender::__construct()
   */
  public function __construct($appenderId, PHI_ParameterHolder $holder)
  {
    parent::__construct($appenderId, $holder);

    $this->_type = $holder->getString('type');
  }

  /**
   * @see PHI_LoggerAppender::write()
   */
  public function write($className, $type, $message)
  {
    $message = $this->getLogFormat($className, $type, $message) . PHP_EOL;

    if ($this->_type === 'stderr') {
      $stream = 'php://stderr';
    } else {
      $stream = 'php://stdout';
    }

    $fp = fopen($stream, 'w');
    fwrite($fp, $message);
    fflush($fp);
    fclose($fp);
  }
}
