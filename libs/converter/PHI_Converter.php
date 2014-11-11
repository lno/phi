<?php
/**
 * データの変換を行うコンバータの抽象クラスです。
 * 全てのコンバータは PHI_Converter を継承する必要があります。
 *
 * コンバータは変換後のデータを {@link PHI_Form フォーム} オブジェクトに格納します。
 * {@link PHI_HttpRequest リクエスト} データ自体が書き換えられることはありません。
 *
 * @package converter
 */
abstract class PHI_Converter extends PHI_Object
{
  /**
   * @var string
   */
  protected $_converterId;

  /**
   * @var PHI_ParameterHolder
   */
  protected $_holder;

  /**
   * コンストラクタ。
   *
   * @param string $converterId コンバータ ID。
   * @param PHI_ParameterHolder $holder パラメータホルダ。
   */
  public function __construct($converterId, PHI_ParameterHolder $holder)
  {
    $this->_converterId = $converterId;
    $this->_holder = $holder;
  }

  /**
   * コンバート処理を行います。
   *
   * @param string $string コンバート対象となる文字列。
   * @return string コンバート後の値を返します。
   */
  abstract public function convert($string);
}
