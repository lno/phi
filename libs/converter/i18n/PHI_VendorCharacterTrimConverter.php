<?php
/**
 * 入力値から機種依存文字 (Windows-31J) を除去します。
 * Unicode 6.0 に対応した絵文字を除去したい場合は、{@link PHI_EmojiTrimConverter} クラスを利用して下さい。
 *
 * ビヘイビアファイルの設定例:
 * <code>
 * convert:
 *   {convert_id}:
 *     names: {field_name,...}
 *     converters:
 *       # コンバータクラス名。
 *       - class: PHI_VendorCharacterTrimConverter
 * </code>
 *
 * @link PHI_VendorCharacterValidator 機種依存とみなされる文字について
 * @package converter.i18n
 */
class PHI_VendorCharacterTrimConverter extends PHI_Converter
{
  /**
   * @see PHI_Converter::__construct()
   */
  public function __construct($converterId, PHI_ParameterHolder $holder)
  {
    parent::__construct($converterId, $holder);

    // mb_convert_encoding() で失敗した文字を削除する
    mb_substitute_character('none');
  }

  /**
   * @see PHI_Converter::convert()
   */
  public function convert($string)
  {
    $detectEncoding = mb_detect_encoding($string, 'UTF-8, SJIS-win');

    if ($detectEncoding !== 'UTF-8') {
      $string = mb_convert_encoding($string, 'UTF-8', $detectEncoding);
    }

    $string = preg_replace('/\p{Co}/u', '', $string);

    $sjisValue = mb_convert_encoding($string, 'Shift_JIS', 'UTF-8');
    $revertValue = mb_convert_encoding($sjisValue, 'UTF-8', 'Shift_JIS');

    return $revertValue;
  }
}
