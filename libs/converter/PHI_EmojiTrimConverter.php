<?php
/**
 * 入力値から絵文字 (Unicode 6.0 Emoji Symbols) を除去します。
 * ヒューチャーフォンの絵文字を除去する場合は {@link PHI_VendorCharacterTrimConverter} クラスを利用して下さい。
 *
 * ビヘイビアファイルの設定例:
 * <code>
 * convert:
 *   {convert_id}:
 *     names: {field_name,...}
 *     converters:
 *       # コンバータクラス名。
 *       - class: PHI_EmojiTrimConverter
 * </code>
 *
 * @package converter
 */
class PHI_EmojiTrimConverter extends PHI_Converter
{
  /**
   * @see PHI_Converter::convert()
   */
  public function convert($string)
  {
    $detectEncoding = mb_detect_encoding($string, 'UTF-8');

    if ($detectEncoding !== 'UTF-8') {
      $string = mb_convert_encoding($string, 'UTF-8', $detectEncoding);
    }

    $pattern = PHI_EmojiValidator::getRegexpPattern();
    $string = preg_replace($pattern, '', $string);

    return $string;
  }
}
