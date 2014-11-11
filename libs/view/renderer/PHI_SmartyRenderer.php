<?php
/**
 * Smarty 描画クラスです。
 * Smarty を有効にするには、base_dicon.yml を次のように設定する必要があります。
 *
 * base_dicon.yml の設定例:
 * <code>
 * componentNames:
 *   view:
 *     class: PHI_View
 *     constructor:
 *       - PHI_SmartyRenderer
 *     setter:
 *       # エスケープ処理は Smarty (escape_html) で行う
 *       autoEscape: FALSE
 *     includes:
 *       - vendors/Smarty/libs/Smarty.php
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
 *   <p>{$greeting}</p>
 * </div>
 * </code>
 *
 * @link http://www.smarty.net/ Smarty
 * @package view.renderer
 */
class PHI_SmartyRenderer extends PHI_Renderer
{
  /**
   * @see PHI_Renderer::getEngine()
   */
  public function getEngine()
  {
    static $engine = NULL;

    if ($engine === NULL) {
      $engine = new Smarty();
      $engine->compile_dir = $this->getCacheDirectory();
      $engine->escape_html = TRUE;
    }

    return $engine;
  }

  /**
   * @see PHI_Renderer::render()
   */
  public function render($data)
  {
    $engine = $this->getEngine();

    foreach ($this->_context['attributes'] as $name => $value) {
      $engine->assign($name, $value);
    }

    $engine->display('string:' . $data);
  }

  /**
   * @see PHI_Renderer::renderFile()
   */
  public function renderFile($path)
  {
    $engine = $this->getEngine();

    foreach ($this->_context['attributes'] as $name => $value) {
      $engine->assign($name, $value);
    }

    $engine->display($path);
  }
}
