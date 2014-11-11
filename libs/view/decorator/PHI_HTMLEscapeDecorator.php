<?php
/**
 * 変数のデータを HTML エスケープする機能を提供します。
 * このクラスは、テンプレートに変数を渡す際に利用する {@link PHI_Renderer::setAttribute()} メソッドや {@link PHI_StringUtils::escape()} といった関数から内部的にコールされて利用されます。
 *
 * @package view.decorator
 */
abstract class PHI_HTMLEscapeDecorator extends PHI_Object
{
  /**
   * @var mixed
   */
  protected $_data;

  /**
   * HTML エスケープされていない生のデータを返します。
   *
   * @return array HTML エスケープされていない生のデータを返します。
   */
  public function getRaw()
  {
    return $this->_data;
  }
}
