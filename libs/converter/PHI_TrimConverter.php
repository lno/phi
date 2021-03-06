<?php
/**
 * 入力された文字列の前後にある空白文字を取り除きます。
 * 除外対象となる文字の一覧は、PHP マニュアルの {@link trim()} 関数を参照して下さい。
 *
 * ビヘイビアファイルの設定例:
 * <code>
 * convert:
 *   {convert_id}:
 *     names: {field_name,...}
 *     converters:
 *       # コンバータクラス名。
 *       - class: PHI_TrimConverter
 *
 *         # 文字列中に含まれる trim 対象文字を除去する場合は TRUE を指定。
 *         inString: FALSE
 *
 *         # 削除対象の文字リスト。(マルチバイト対応)
 *         # デフォルトの削除対象文字は PHP マニュアルの {@link trim()} 関数を参照。
 *         # charList に指定した文字は、デフォルトの削除対象文字に加え削除対象となる。
 *         charList: {array}
 * </code>
 * @package converter
 */
class PHI_TrimConverter extends PHI_Converter
{
  /**
   * @var string
   */
  private $_trimChars = " \t\n\r\0\x0B";

  /**
   * @var array
   */
  private $_trimCharList = array(' ', "\t", "\n", "\r", "\0", "\x0B");

  /**
   * @var bool
   */
  private $_inString = FALSE;

  /**
   * @see PHI_Converter::__construct()
   */
  public function __construct($converterId, PHI_ParameterHolder $holder)
  {
    parent::__construct($converterId, $holder);

    $charList = $holder->getArray('charList');

    if ($holder->getBoolean('inString')) {
      $this->_inString = TRUE;

      if (is_array($charList)) {
        $this->_trimCharList = array_merge($this->_trimCharList, $charList);
      }

    } else if (is_array($charList)) {
      $this->_trimChars .= implode($charList);
    }
  }

  /**
   * @see PHI_Converter::convert()
   */
  public function convert($string)
  {
    if ($this->_inString) {
      $string = str_replace($this->_trimCharList, '', $string);

    } else {
      $trimChars = $this->_trimChars;
      $string = PHI_StringUtils::trim($string, $this->_trimChars);
    }

    return $string;
  }
}
