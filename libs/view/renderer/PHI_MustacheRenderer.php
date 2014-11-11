<?php
/**
 * Mustache 描画クラスです。
 * Mustache を有効にするには、base_dicon.yml を次のように設定する必要があります。
 *
 * base_dicon.yml の設定例:
 * <code>
 * componentNames:
 *   view:
 *     class: PHI_View
 *     constructor:
 *       - PHI_MustacheRenderer
 *     setter:
 *       # エスケープは Mustache で行う
 *       autoEscape: FALSE
 *     includes:
 *       - vendors/mustache.php/src/Mustache/Autoloader.php
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
 *   <p>{{greeting}}</p>
 * </div>
 * </code>
 *
 * @link https://github.com/bobthecow/mustache.php mustache
 * @package view.renderer
 */
class PHI_MustacheRenderer extends PHI_Renderer
{
  /**
   * @see PHI_Renderer::__construct()
   */
  public function __construct()
  {
    parent::__construct();

    Mustache_Autoloader::register();
  }

  /**
   * @see PHI_Renderer::getEngine()
   */
  public function getEngine()
  {
    static $engine;

    if ($engine === NULL) {
      $response = PHI_FrontController::getInstance()->getResponse();

      $config = array();
      $config['charset'] = $response->getOutputEncoding();
      $config['cache'] = $this->getCacheDirectory();

      $engine = new Mustache_Engine($config);
    }

    return $engine;
  }

  /**
   * @see PHI_Renderer::display()
   */
  public function render($data)
  {
    echo $this->getEngine()->render($data, $this->_context['attributes']);
  }

  /**
   * @see PHI_Renderer::renderFile()
   */
  public function renderFile($path)
  {
    $extension = PHI_Config::getApplication()->getString('view.extension');

    $options = array('extension' => $extension);
    $loader = new Mustache_Loader_FilesystemLoader(dirname($path), $options);

    $engine = $this->getEngine();
    $engine->setLoader($loader);

    $info = pathinfo($path);

    $template = $this->getEngine()->loadTemplate($info['filename']);
    echo $template->render($this->_context['attributes']);
  }
}
