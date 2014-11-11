<?php
/**
 * データベースの式を {@link PHI_Entity} のプロパティにセットする際に使用します。
 *
 * @package database
 */
class PHI_DatabaseExpression extends PHI_Object
{
  /**
   * データベースが理解可能な式や値。
   */
  private $_expression;

  /**
   * コンストラクタ。
   *
   * @param string $expressin データベースが理解可能な式や値。
   */
  public function __construct($expression)
  {
    $this->_expression = $expression;
  }

  /**
   * NULL 値を表現します。
   *
   * @return PHI_DatabaseExpression NULL 値を表すインスタンスを返します。
   */
  public static function null()
  {
    return new PHI_DatabaseExpression('NULL');
  }

  /**
   * 式を文字列形式で取得します。
   *
   * @return string 式を文字列形式で返します。
   */
  public function __toString()
  {
    return $this->_expression;
  }
}
