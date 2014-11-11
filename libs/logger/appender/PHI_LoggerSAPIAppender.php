<?php
/**
 * ロギングしたメッセージを SAPI へ送信します。
 * 通常は Web サーバのエラーとしてロギングしますが、CLI から実行した場合は標準エラーとして出力します。
 * Web サーバが Apache 2 の場合、ログはデフォルトで "{Apache のインストールパス}/logs/error_log" に出力されます。
 *
 * application.yml の設定例:
 * <code>
 * logger:
 *   # ログアペンダ ID。
 *   {appender_name}:
 *     # ロガークラス名。
 *     class: PHI_LoggerSAPIAppender
 * </code>
 * <i>その他に指定可能な属性は {@link PHI_LoggerAppender} クラスを参照して下さい。</i>
 *
 * @package logger.appender
 */
class PHI_LoggerSAPIAppender extends PHI_LoggerAppender
{
  /**
   * @see PHI_LoggerAppender::write()
   */
  public function write($className, $type, $message)
  {
    // error_log() が改行を含めるため、getLogFormat() には改行を追加しない
    $message = $this->getLogFormat($className, $type, $message);
    error_log($message, 0);
  }
}
