<?php
/**
 * アプリケーションで発生した全ての例外を捕捉する例外ハンドラーです。
 * 例外を独自にハンドリングしたい場合は、{@link PHI_ExceptionDelegate} を実装したクラスを作成する必要があります。
 *
 * @package kernel.handler
 */
class PHI_ExceptionHandler
{
  /**
   * 例外発生時にハンドリングされるメソッドです。
   * 発生した例外をキャッチする子ハンドラが存在する場合は処理を子ハンドラに移します。
   *
   * @param Exception $exception {@link Exception}、または Exception を継承した例外オブジェクトのインスタンス。
   */
  public static function handler(Exception $exception)
  {
    try {
      $exceptionConfig = PHI_Config::getApplication()->get('exception');
      $match = FALSE;

      if (sizeof($exceptionConfig)) {
        foreach ($exceptionConfig as $attributes) {
          $types = explode(',', $attributes->get('type'));
          $match = FALSE;

          foreach ($types as $type) {
            $type = trim($type);

            if (class_exists($type) && $exception instanceof $type) {
              $match = TRUE;
              break;
            }
          }

          if ($match) {
            $callback = array($attributes->get('delegate'), 'invoker');
            forward_static_call($callback, $exception, $attributes);

            // 例外の継続
            if (!$attributes->getBoolean('continue')) {
              break;
            }
          }
        }

      } else {
        $message ="Application exception occurred. "
                 ."But exception delegate is undefined. Please define a delegate.\n";
        PHI_ErrorHandler::invokeFatalError(E_ERROR, $message, __FILE__, __LINE__);
      }

      if (PHI_BootLoader::isBootTypeWeb()) {
        $buffer = ob_get_contents();
        ob_end_clean();

        $arguments = array(&$buffer);
        $controller = PHI_FrontController::getInstance();
        $controller->getObserver()->dispatchEvent('preOutput', $arguments);

        $response = $controller->getResponse();
        $response->write($buffer);
        $response->flush();
      }

    // 例外ハンドラ内で起こる全ての例外を捕捉
    } catch (Exception $e2) {
      try {
        // 可能な限りスタックトレースを出力
        PHI_ExceptionStackTraceDelegate::invoker($e2);

      } catch (Exception $e3) {
        PHI_ErrorHandler::invokeFatalError(
          E_ERROR,
          $exception->getMessage(),
          $exception->getFile(),
          $exception->getLine()
        );
      }
    }

    // 例外ハンドリング後に "Fatal error" が生成されないようプログラムを強制終了する
    // 'require' はファイルが見つからない場合に 'Warning' と 'Fatal' エラーを生成するが、
    // PHI_ErrorHandler::handler() が検知するのは Warning であり、Fatal を検知するのは
    // PHI_ErrorHandler::detectFatalError() メソッドとなる
    die();
  }
}
