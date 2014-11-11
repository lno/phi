<?php
/**
 * Twig 描画クラスです。
 * Twig を有効にするには、base_dicon.yml を次のように設定する必要があります。
 *
 * base_dicon.yml の設定例:
 * <code>
 * componentNames:
 *   view:
 *     class: PHI_View
 *     constructor:
 *       - PHI_TwigRenderer
 *     setter:
 *       # エスケープ処理は Twig (Twig_Extension_Escaper) で行う
 *       autoEscape: FALSE
 *     includes:
 *       - vendors/Twig/lib/Twig/Autoloader.php
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
 * @link http://www.twig-project.org/ Twig
 * @package view.renderer
 */
class PHI_TwigRenderer extends PHI_Renderer
{
  /**
   * @see PHI_Renderer::__construct()
   */
  public function __construct()
  {
    parent::__construct();

    Twig_Autoloader::register();
  }

  /**
   * @see PHI_Renderer::getEngine()
   */
  public function getEngine()
  {
    static $engine = NULL;

    if ($engine === NULL) {
      $response = PHI_FrontController::getInstance()->getResponse();

      $config = array();
      $config['cache'] = $this->getCacheDirectory();
      $conifg['charset'] = $response->getOutputEncoding();
      $config['auto_reload'] = TRUE;
      $config['autoescape'] = FALSE;

      $escaper = new Twig_Extension_Escaper(TRUE);

      $engine = new Twig_Environment(NULL, $config);
      $engine->addExtension($escaper);
    }

    return $engine;
  }

  /**
   * @see PHI_Renderer::render()
   */
  public function render($data)
  {
    $engine = $this->getEngine();
    $engine->setLoader(new Twig_Loader_String());

    echo $engine->loadTemplate($data)->render($this->_context['attributes']);
  }

  /**
   * @see PHI_Renderer::renderFile()
   */
  public function renderFile($path)
  {
    $engine = $this->getEngine();
    $engine->setLoader(new Twig_Loader_Filesystem(dirname($path)));

    echo $engine->render(basename($path), $this->_context['attributes']);
  }
}
