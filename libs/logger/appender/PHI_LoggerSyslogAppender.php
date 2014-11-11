<?php
/**
 * ロギングしたメッセージを SYSLOG へ送信します。
 * Windows 環境では、SYSLOG サービスはイベントログとして扱われます。
 *
 * application.yml の設定例:
 * <code>
 * logger:
 *   # ログアペンダ ID。
 *   {appender_name}:
 *     # ロガークラス名。
 *     class: PHI_LoggerSyslogAppender
 *
 *     # システムログの各メッセージに追加される文字列。
 *     identity:
 *
 *     # ロギング用オプション。
 *     # 詳しくは {@link openlog()} 関数を参照。
 *     openlog:
 * </code>
 * <i>その他に指定可能な属性は {@link PHI_LoggerAppender} クラスを参照して下さい。</i>
 *
 * ログレベルと SYSLOG レベルは次のようにマッピングされます。
 *   - {@link PHI_Logger::LOGGER_MASK_TRACE}: LOG_NOTICE
 *   - {@link PHI_Logger::LOGGER_MASK_DEBUG}: LOG_DEBUG
 *   - {@link PHI_Logger::LOGGER_MASK_INFO}: LOG_INFO
 *   - {@link PHI_Logger::LOGGER_MASK_WARNING}: LOG_WARNING
 *   - {@link PHI_Logger::LOGGER_MASK_ERROR}: LOG_ERR
 *   - {@link PHI_Logger::LOGGER_MASK_FATAL}: LOG_CRIT
 *
 * <i>LOG_EMERG、LOG_ALERT はマッピングされません。</i>
 * @package logger.appender
 */
class PHI_LoggerSyslogAppender extends PHI_LoggerAppender
{
  /**
   * SYSLOG 定数とフレームワークのエラー定数をマッピング。
   * @var array
   */
  private static $_mapping = array(
    PHI_Logger::LOGGER_MASK_TRACE => LOG_NOTICE,
    PHI_Logger::LOGGER_MASK_DEBUG => LOG_DEBUG,
    PHI_Logger::LOGGER_MASK_INFO => LOG_INFO,
    PHI_Logger::LOGGER_MASK_WARNING => LOG_WARNING,
    PHI_Logger::LOGGER_MASK_ERROR => LOG_ERR,
    PHI_Logger::LOGGER_MASK_FATAL => LOG_CRIT
  );

  /**
   * @see PHI_LoggerAppender::__construct()
   */
  public function __construct($appenderId, PHI_ParameterHolder $holder)
  {
    parent::__construct($appenderId, $holder);

    $openlog = $holder->getInt('openlog');

    if ($openlog !== NULL) {
      $option = $openlog;
    } else {
      $option = LOG_ODELAY;
    }

    $identity = $holder->getBoolean('identity');
    openlog($identity, $option, LOG_SYSLOG);
  }

  /**
   * @see PHI_LoggerAppender::write()
   */
  public function write($className, $type, $message)
  {
    $message = $this->getLogFormat($className, $type, $message);
    syslog(self::$_mapping[$type], $message);
  }
}
