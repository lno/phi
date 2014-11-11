<?php
/**
 * 例外が発生した際に、アプリケーションの実行環境に合わせて適切なビューを出力します。
 *
 * Web 環境で例外が発生した場合:
 * <ul>
 *   <li>
 *     デバッグモード無効時:
 *     システムエラー (例外) が発生した旨を一般的なクライアントに通知するためのお知らせページ (テンプレート) を出力する。
 *     テンプレート内では 'exception' 変数 (例外オブジェクトを格納した変数) が使用可能。
 *     <ul>
 *       <li>
 *         AJAX リクエスト ({@link PHI_HttpRequest::isAjax()}) で例外が発生した場合は、以下のデータを JSON 形式で返す。
 *         <ul>
 *           <li>type: 例外クラス名</li>
 *           <li>message: 例外メッセージ ({@link Exception::getMessage()})</li>
 *           <li>code: 例外コード ({@link Exception::getCode()})</li>
 *         </ul>
 *       </li>
 *     </ul>
 *   </li>
 *   <li>
 *     デバッグモード有効時:
 *     スタックトレースを出力する。({@link PHI_ExceptionStackTraceDelegate::catchOnWeb()} と同じ動作)
 *     <ul>
 *       <li>
 *         AJAX リクエスト発生時は、'type'、'message'、'code' に加えて次のデータが返される
 *         <ul>
 *           <li>file: 例外ファイル名 ({@link Exception::getFile()})</li>
 *           <li>line: 例外が発生した行数 ({@link Exception::getCode()})</li>
 *         </ul>
 *       </li>
 *     </ul>
 *   </li>
 * </ul>
 *
 * コンソール環境で例外が発生した場合:
 * <ul>
 *   <li>
 *     例外のスタックトレースを出力する。これは {@link PHI_ExceptionStackTraceDelegate::catchOnConsole()} と同じ動作となる。
 *   </li>
 * </ul>
 *
 * application.yml の設定例:
 * <code>
 * exception:
 *   # 対象とする例外 (Exception 指定時は全ての例外を捕捉)
 *   - type: Exception
 *
 *     # 例外委譲クラスの指定
 *     delegate: PHI_ExceptionOutputDelegate
 *
 *     # 送信する HTTP ステータス
 *     htptStatus: 500
 *
 *     # AJAX リクエストで例外が発生した場合のレスポンス形式
 *     ajaxResponse:
 *       # データフォーマット
 *       #   - json: JSON 形式
 *       #   - text: 'type,message,code' から構成される文字列形式 (デバッグ時は末尾に ',file,line' を追加)
 *       type: json
 * </code>
 *
 * システムエラーの出力に使用されるテンプレートは {APP_ROOT_DIR}/templates/html/system_error.php にあります。
 * 尚、フレームワークが提供するヘルパのインスタンスはテンプレートに割り当てられないため、ヘルパメソッドを使用することはできません。
 *
 * @package exception.delegate
 */
class PHI_ExceptionOutputDelegate extends PHI_ExceptionStackTraceDelegate
{
  /**
   * @see PHI_ExceptionDelegate::catchOnApplication()
   */
  protected static function catchOnApplication(Exception $exception, PHI_ParameterHolder $holder = NULL)
  {
    parent::clearBuffer();
  }

  /**
   * @see PHI_ExceptionDelegate::catchOnWeb()
   */
  protected static function catchOnWeb(Exception $exception, PHI_ParameterHolder $holder)
  {
    $httpStatus = $holder->getInt('httpStatus', 500);

    $controller = PHI_FrontController::getInstance();
    $response = $controller->getResponse();
    $response->setStatus($httpStatus);

    if ($controller->getRequest()->isAjax()) {
      self::sendAJAXResponse($response, $exception, $holder);

    } else {
      if (PHI_DebugUtils::isDebug()) {
        PHI_ExceptionStackTraceDelegate::invoker($exception, $holder);

      } else {
        self::sendWebResponse($response, $exception, $holder);
      }
    }
  }

  /**
   * @see PHI_ExceptionDelegate::catchOnConsole()
   */
  protected static function catchOnConsole(Exception $exception, PHI_ParameterHolder $holder)
  {
    parent::catchOnConsole($exception, $holder);
  }

  /**
   * Web 環境で発生した例外をクライアントに通知します。
   *
   * @param PHI_HttpResponse $response レスポンスオブジェクト。
   * @param Exception $exception 例外オブジェクト。
   * @param PHI_ParameterHolder $holder パラメータホルダ。
   */
  protected static function sendWebResponse(PHI_HttpResponse $response, Exception $exception, PHI_ParameterHolder $holder)
  {
    $path = sprintf('%s%shtml%ssystem_error.php',
      PHI_AppPathManager::getInstance()->getTemplatesPath(),
      DIRECTORY_SEPARATOR,
      DIRECTORY_SEPARATOR);

    $view = new PHI_View(new PHI_BaseRenderer());
    $view->setAttribute('exception', $exception);
    $view->setTemplatePath($path);
    $view->importHelpers();
    $view->execute();
  }

  /**
   * AJAX リクエストで発生した例外をクライアントに通知します。
   *
   * @param PHI_HttpResponse $response レスポンスオブジェクト。
   * @param Exception $exception 例外オブジェクト。
   * @param PHI_ParameterHolder $holder パラメータホルダ。
   */
  protected static function sendAJAXResponse(PHI_HttpResponse $response, Exception $exception, PHI_ParameterHolder $holder)
  {
    $ajaxResponse = $holder->get('ajaxResponse', array());
    $type = $ajaxResponse->getString('type', 'json');

    $data = array();
    $data['type'] = get_class($exception);
    $data['message'] = $exception->getMessage();
    $data['code'] = $exception->getCode();

    if (PHI_DebugUtils::isDebug()) {
      $data['file'] = $exception->getFile();
      $data['line'] = $exception->getLine();
    }

    if ($type === 'json') {
      $response->writeJSON($data);

    } else {
      $data['message'] = '"' . addslashes($data['message']) . '"';
      $string = implode(',', $data);

      $response->write($string);
    }
  }
}
