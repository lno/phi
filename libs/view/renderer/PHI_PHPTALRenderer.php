<?php
/**
 * PHPTAL 描画クラスです。
 * PHPTAL を有効にするには、base_dicon.yml を次のように設定する必要があります。
 *
 * base_dicon.yml の設定例:
 * <code>
 * componentNames:
 *   view:
 *     class: PHI_View
 *     constructor:
 *       - PHI_PHPTALRenderer
 *     setter:
 *       # エスケープ処理は PHPTAL で行う
 *       autoEscape: FALSE
 *     includes:
 *       - vendors/PHPTAL/PHPTAL.php
 * </code>
 *
 * ビューに変数を設定:
 * <code>
 * $view->setAttribute('greeting', 'Hello World!');
 * </code>
 *
 * テンプレートの実装例:
 * <code>
 * <div id="content">
 *   <p tal:content="var"></p>
 * </div>
 *
 * @link http://phptal.org/ PHPTAL
 * @package view.renderer
 */
class PHI_PHPTALRenderer extends PHI_Renderer
{
  /**
   * @see PHI_Renderer::getEngine()
   */
  public function getEngine()
  {
    static $engine = NULL;

    if ($engine === NULL) {
      $response = PHI_FrontController::getInstance()->getResponse();

      $engine = new PHPTAL();
      $engine->setEncoding($response->getOutputEncoding());
    }

    return $engine;
  }

  /**
   * @see PHI_Renderer::render()
   */
  public function render($data)
  {
    $engine = $this->getEngine();
    $engine->setSource($data);

    foreach ($this->_context['attributes'] as $name => $value) {
      $engine->set($name, $value);
    }

    $engine->echoExecute();
  }

  /**
   * @see PHI_Renderer::renderFile()
   */
  public function renderFile($path)
  {
    $engine = $this->getEngine();
    $engine->setTemplate($path);

    foreach ($this->_context['attributes'] as $name => $value) {
      $engine->set($name, $value);
    }

    $engine->echoExecute();
  }
}
