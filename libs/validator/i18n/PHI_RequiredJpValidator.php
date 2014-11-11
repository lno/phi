<?php
/**
 * 日本語に対応した空文字検証のためのバリデータです。
 *
 * ビヘイビアファイルの設定例:
 * <code>
 * validate:
 *   {validator_id}:
 *     # バリデータクラス名。
 *     class: PHI_RequiredJpValidator
 *
 *     # 全角空白文字 (\x81\x40) に対応。基本機能は親クラスに準ずる。
 *     whitespace:
 * </code>
 *
 * @package validator.i18n
 */
class PHI_RequiredJpValidator extends PHI_RequiredValidator
{
  /**
   * @see PHI_RequiredValidator::hasWhitespace()
   */
  public function hasWhitespace($value)
  {
    $encoding = PHI_Config::getApplication()->get('charset.default');
    $value = mb_convert_encoding($value, 'Shift_JIS', $encoding);

    // \x81\x40: 全角スペース
    if (preg_match('/^(\s|\x81[\x40])+$/', $value)) {
      return TRUE;
    }

    return FALSE;
  }
}
