<?php
/**
 * 対象文字列に機種依存文字 (Windows-31J) が含まれていないか検証します。
 * 対象となる文字は、ヒューチャーフォンのキャリア (docomo、AU、SoftBank) が定義した絵文字コードを含みます。
 * Unicode 6.0 に対応した絵文字を検証したい場合は、{@link PHI_EmojiValidator} クラスを利用して下さい。
 *
 * ビヘイビアファイルの設定例:
 * <code>
 * validate:
 *   {validator_id}:
 *     # バリデータクラス名。
 *     class: PHI_VendorCharacterValidator
 *
 *     # 機種依存文字が含まれる場合に通知するエラーメッセージ。
 *     matchError: {default_message}
 * </code>
 *
 * @package validator.i18n
 */
class PHI_VendorCharacterValidator extends PHI_Validator
{
  /**
   * 文字列内に機種依存文字が含まれていないかチェックします。
   *
   * @param string $value チェック対象の文字列。
   * @return bool 機種依存文字が含まれない場合に TRUE、含まれる場合は FALSE を返します。
   */
  public static function isValid($value)
  {
    $detectEncoding = mb_detect_encoding($value, 'UTF-8, SJIS-win');

    if ($detectEncoding !== 'UTF-8') {
      $value = mb_convert_encoding($value, 'UTF-8', $detectEncoding);
    }

    // 私用領域 (Private Use) が使われてる場合は依存文字が含まれてるとみなす
    // (ヒューチャーフォンの絵文字変換ライブラリ等を使用した場合、SJIS-win から UTF-8 へ変換する際に Unicode の私用領域が使われる場合がある)
    if (preg_match('/\p{Co}/u', $value)) {
      return FALSE;

    } else {
      // Windows-31J の文字を検知する
      $sjisValue = mb_convert_encoding($value, 'Shift_JIS', 'UTF-8');
      $revertValue = mb_convert_encoding($sjisValue, 'UTF-8', 'Shift_JIS');

      if ($value !== $revertValue) {
        return FALSE;
      }
    }

    return TRUE;
  }

  /**
   * @see PHI_Validator::validate()
   */
  public function validate($fieldName, $value, array $variables = array())
  {
    $holder = $this->buildParameterHolder($variables);

    if (strlen($value) == 0) {
      return TRUE;
    }

    if (self::isValid($value)) {
      return TRUE;
    }

    $message = $holder->getString('matchError');

    if ($message === NULL) {
      $message = sprintf('Character format is illegal. [%s]', $fieldName);
    }

    $this->sendError($fieldName, $message);

    return FALSE;
  }
}
