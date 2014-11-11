<?php
/**
 * phi が提供する標準の描画クラスです。
 * このクラスは生の PHP コードでテンプレートを作成することができます。
 *
 * ビューに変数を設定:
 * <code>
 * $view->setAttribute('greeting', 'Hello World!');
 * </code>
 *
 * テンプレートの実装例:
 * <code>
 * <div id="content">
 *   <?php echo $form->start() ?>
 *     <p><?php echo $greeting ?></p>
 *   <?php echo $form->close() ?>
 * </div>
 * </code>
 *
 * @package view.renderer
 */
class PHI_BaseRenderer extends PHI_Renderer
{
  /**
   * @see PHI_Renderer::getEngine()
   */
  public function getEngine()
  {
    throw new RuntimeException('Rendering engine is not defined.');
  }

  /**
   * @see PHI_Renderer::render()
   */
  public function render($data)
  {
    $parser = new PHI_PHPStringParser($data, $this->_context['attributes']);
    $parser->execute();
    $parser->output();
  }

  /**
   * @see PHI_Renderer::renderFile()
   */
  public function renderFile($path)
  {
    // $this->_context['attributes'] には 'path' 変数が含まれる可能性もあるため、名前を変えておく
    $_path = $path;

    extract($this->_context['helpers']);
    extract($this->_context['attributes']);

    require $_path;
  }
}

