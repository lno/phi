<?php
/**
 * 入力された文字列を指定した文字で分割、配列型に変換します。
 *
 * ビヘイビアファイルの設定例:
 * <code>
 * convert:
 *   {convert_id}:
 *     names: {field_name,...}
 *     converters:
 *       # コンバータクラス名。
 *       - class: PHI_SplitConverter
 *
 *         # 変換対象のフィールド名。
 *         from:
 *
 *         # separator で分割した結果を格納するフィールド名。未指定の場合は 'from' に格納される。
 *         to:
 *
 *         # 文字列を分割するセパレータ。
 *         # 既定値は ',' (カンマ)。',; ' のように複数指定することも可能。
 *         # 分割した各要素には trim() 関数が適用され、空となった要素は結果から除外される。
 *         separator:
 * </code>
 * @package converter
 */
class PHI_SplitConverter extends PHI_Converter
{
  /**
   * @var string
   */
  private $_from;

  /**
   * @var string
   */
  private $_to;

  /**
   * @var string
   */
  private $_pattern;

  /**
   * @throws PHI_ConfigurationException コンバータの設定に問題がある場合に発生。
   * @see PHI_Converter::__construct()
   */
  public function __construct($converterId, PHI_ParameterHolder $holder)
  {
    parent::__construct($converterId, $holder);

    $this->_from = $holder->getString('from');

    if (PHI_StringUtils::nullOrEmpty($this->_from)) {
      $message = sprintf('"from" attribute is not specified. [%s]', $converterId);
      throw new PHI_ConfigurationException($message);
    }

    $this->_to = $holder->getString('to', $this->_from);
    $separator = $holder->getString('separator', ',');

    if (strlen($separator) == 0) {
      $message = sprintf('"separator" attribute is invalid. [%s]', $converterId);
      throw new PHI_ConfigurationException($message);
    }

    $this->_pattern = '/[' . preg_quote($separator, '/') . ']/';
  }

  /**
   * @see PHI_Converter::convert()
   */
  public function convert($string)
  {
    $form = PHI_ActionForm::getInstance();
    $array = preg_split($this->_pattern, $form->get($this->_from));

    $split = array();
    $j = sizeof($array);

    for ($i = 0; $i < $j; $i++) {
      $temp = trim($array[$i]);

      if (strlen($temp)) {
        $split[] = $temp;
      }
    }

    $form->set($this->_to, $split);
  }
}
