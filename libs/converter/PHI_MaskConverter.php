<?php
/**
 * 正規表現による文字列の変換を行います。
 *
 * ビヘイビアファイルの設定例:
 * <code>
 * convert:
 *   {convert_id}:
 *     names: {field_name,...}
 *     converters:
 *       # コンバータクラス名。
 *       - class: PHI_MaskConverter
 *
 *         # 置換対象の文字列パターン。
 *         # Perl 互換の正規表現が使用可能。
 *         pattern:
 *
 *         # 置換後の文字列。
 *         replace: ''
 * </code>
 * @package converter
 */
class PHI_MaskConverter extends PHI_Converter
{
  /**
   * @var string
   */
  private $_pattern;

  /**
   * @var string
   */
  private $_replace;

  /**
   * @throws PHI_ConfigurationException コンバータの設定に問題がある場合に発生。
   * @see PHI_Converter::__construct()
   */
  public function __construct($converterId, PHI_ParameterHolder $holder)
  {
    parent::__construct($converterId, $holder);

    $this->_pattern = $holder->getString('pattern');
    $this->_replace = $holder->getString('replace');

    if (!preg_match('/^\/.+\/$/', $this->_pattern)) {
      $message = sprintf('"pattern" attribute is invalid. [%s]', $converterId);
      throw new PHI_ConfigurationException($message);
    }
  }

  /**
   * @see PHI_Converter::convert()
   */
  public function convert($string)
  {
    $convert = preg_replace($this->_pattern, $this->_replace, $string);

    return $convert;
  }
}
