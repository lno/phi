<?php
/**
 * Web アプリケーションのためのイベントリスナです。
 *
 * @package kernel.observer.listener
 */
class PHI_WebApplicationEventListener extends PHI_ApplicationEventListener
{
  /**
   * array('{@link preOutput() preOutput}') を取得します。
   *
   * @see PHI_ApplicationEventListener::getListenerEvents()
   */
  public function getListenEvents()
  {
    return array('preOutput');
  }

  /**
   * @see PHI_ApplicationEventListener::getBootMode()
   */
  public function getBootMode()
  {
    return PHI_BootLoader::BOOT_MODE_WEB;
  }

  /**
   * URI からルートが確定したタイミング (アクションのインスタンスが生成される前) で起動します。
   *
   */
  public function postRouteConnect()
  {}

  /**
   * コンテンツが出力される直前に起動します。
   * 内部エンコーディングと {@link PHI_HttpResponse::setOutputEncoding()} で指定されたエンコーディングが異なる場合、エンコードの変換が行われます。
   *
   * @param string &$contents 出力するコンテンツ。
   */
  public function preOutput(&$contents)
  {
    $response = PHI_FrontController::getInstance()->getResponse();

    if ($response->hasBinary()) {
      $contents = $response->getWriteBuffer();

    } else {
      $internalEncoding = PHI_Config::getApplication()->get('charset.default');
      $outputEncoding = $response->getOutputEncoding();

      if ($internalEncoding !== $outputEncoding) {
        $contents = mb_convert_encoding($contents, $outputEncoding, $internalEncoding);
      }
    }
  }
}
