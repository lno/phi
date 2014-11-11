<?php
/**
 * アプリケーションで発生した例外をスタックトレース形式で出力します。
 * 本ハンドラを有効にするには、あらかじめ application.yml に次の記述を定義しておく必要があります。
 *
 * <strong>このクラスはデバッグモード有効時のみスタックトレースを出力します。
 * デバッグ無効時は例外メッセージが {@link PHI_ErrorHandler::invokeFatalError()} メソッドに送信される点に注意して下さい。</strong>
 *
 * application.yml の設定例:
 * <code>
 * exception:
 *   # 対象とする例外 (Exception 指定時は全ての例外を捕捉)
 *   - type: Exception
 *
 *     # 例外委譲クラスの指定
 *     delegate: PHI_ExceptionStackTraceDelegate
 * </code>
 *
 * @package exception.delegate
 */
class PHI_ExceptionStackTraceDelegate extends PHI_ExceptionDelegate
{
  /**
   * @see PHI_ExceptionDelegate::catchOnApplication()
   */
  protected static function catchOnApplication(Exception $exception, PHI_ParameterHolder $holder)
  {
    self::clearBuffer();

    if (!PHI_DebugUtils::isDebug()) {
      PHI_ErrorHandler::invokeFatalError(E_ERROR,
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine());
    }
  }

  /**
   * バッファに含まれる全てのデータを破棄します。
   */
  protected static function clearBuffer()
  {
    if (PHI_BootLoader::isBootTypeWeb()) {
      $response = PHI_FrontController::getInstance()->getResponse();
      $response->clear();
    }

    $i = ob_get_level();

    for (; $i > 1; $i--) {
      ob_end_clean();
    }

    ob_start();
  }

  /**
   * Web アプリケーションで発生した例外のスタックトレースを出力します。
   *
   * @see PHI_ExceptionDelegate::catchOnWeb()
   */
  protected static function catchOnWeb(Exception $exception, PHI_ParameterHolder $holder)
  {
    $internalEncoding = PHI_Config::getApplication()->get('charset.default');
    $trace = $exception->getTrace();
    $message = $exception->getMessage();

    // ソケット通信エラーなど OS から直接エラーが返された場合、例外メッセージが UTF-8 と異なる場合がある
    if (!mb_check_encoding($message, $internalEncoding)) {
      // 可能な限り文字コードの判定を行う
      $detectEncoding = mb_detect_encoding($message, 'ASCII, JIS, UTF-8, EUC-JP, SJIS');
      $message = mb_convert_encoding($message, $internalEncoding, $detectEncoding);
    }

    $inspector = new PHI_CodeInspector();
    $code = $inspector->buildFromException($exception);

    $view = new PHI_View(new PHI_BaseRenderer());
    $view->setAttribute('exception', $exception, FALSE);
    $view->setAttribute('message', $message);

    if ($internalEncoding === $internalEncoding) {
      $trace = $code;
    } else {
      $trace = mb_convert_encoding($code, $internalEncoding, $internalEncoding);
    }

    $view->setAttribute('trace', $trace, FALSE);

    $path = PHI_ROOT_DIR . '/skeleton/templates/exception.php';

    $view->setTemplatePath($path);
    $view->execute();
  }

  /**
   * コンソールアプリケーションで発生した例外のスタックトレースを出力します。
   *
   * @see PHI_ExceptionDelegate::catchOnConsole()
   */
  protected static function catchOnConsole(Exception $exception, PHI_ParameterHolder $holder)
  {
    $className = get_class($exception);

    $trace = $exception->getTrace();
    $message = $exception->getMessage();

    $directlyFactor = $trace[0];
    $file = $exception->getFile();
    $line = $exception->getLine();
    $referenceCode = TRUE;

    $buffer = sprintf(
      "%s: %s\n  at %s%s()\n",
      $className,
      $message,
      isset($directlyFactor['class']) ? $directlyFactor['class'] . '#' : '',
      $directlyFactor['function']);

    if (strcmp($className, 'ErrorException') != 0) {
      $buffer .= sprintf("  in %s [Line: %s]\n", $file, $line);
      $applicationFactor = array();

      // PHI_LIBS_DIR の DIRECTORY_SEPARATOR を OS の標準に合わせておく
      $phiLibsDir = str_replace('/', DIRECTORY_SEPARATOR, PHI_LIBS_DIR);

      // フレームワーク内部で例外が発生した場合はトレース情報から実行元を探す
      if (strpos($file, $phiLibsDir) === 0) {
        foreach ($trace as $stack) {
          if (isset($stack['file']) && strpos($stack['file'], $phiLibsDir) === 0) {
            continue;
          } else if (isset($stack['class']) && strpos($stack['class'], 'PHI_') === 0) {
            continue;
          }

          $applicationFactor = $stack;
          break;
        }

        if (empty($applicationFactor['file'])) {
          $referenceCode = FALSE;;

        } else {
          $file = $applicationFactor['file'];
          $line = $applicationFactor['line'];
        }

      } else {
        $referenceCode = FALSE;
      }
    }

    if ($referenceCode) {
      $buffer .= sprintf("  (ref: %s [Line: %d])\n", $file, $line);
    }

    $buffer = PHI_ANSIGraphic::build($buffer, PHI_ANSIGraphic::FOREGROUND_RED|PHI_ANSIGraphic::ATTRIBUTE_BOLD);
    error_log($buffer, 4);
  }
}
