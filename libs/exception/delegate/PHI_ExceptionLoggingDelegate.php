<?php
/**
 * アプリケーションで発生した例外の原因と発生箇所を {@link PHI_Logger} 経由でファイルシステムや標準出力にロギングします。
 * 本ハンドラを有効にするには、あらかじめ application.yml に次の記述を定義しておく必要があります。
 *
 * application.yml の設定例:
 * <code>
 * exception:
 *   # 対象とする例外。(Exception 指定時は全ての例外を捕捉)
 *   - type: {@link Exception}
 *
 *     # 例外委譲クラスの指定。
 *     delegate: {@link PHI_ExceptionLoggingDelegate}
 *
 *     # ログレベルの指定。(オプション)
 *     level: <?php echo {@link PHI_Logger::LOGGER_MASK_ERROR} ?>
 * </code>
 *
 * application.yml の設定例:
 * <code>
 * # ログアペンダの定義。
 * logger:
 *   errorFileAppender:
 *     mask: <?php echo PHI_Logger::LOGGER_MASK_ERROR ?>
 *     class: {@link PHI_LoggerFileAppender}
 *     file: error.log
 *     rotate:
 *       type: date
 *       datePattern: Y-m
 * </code>
 *
 * @package exception.delegate
 */
class PHI_ExceptionLoggingDelegate extends PHI_ExceptionDelegate
{
  /**
   * @see PHI_ExceptionDelegate::catchOnApplication()
   */
  protected static function catchOnApplication(Exception $exception, PHI_ParameterHolder $holder)
  {
    $level = $holder->getInt('level', PHI_Logger::LOGGER_MASK_ERROR);
    $logger = PHI_Logger::getLogger(get_class($exception));

    call_user_func_array(array($logger, 'send'), array($level, $exception));
  }
}
