<?php
/**
 * 対象文字列に絵文字 (Unicode 6.0 Emoji Symbols) が含まれていないか検証します。
 * ヒューチャーフォンの絵文字を検証する場合は {@link PHI_VendorCharacterValidator} クラスを利用して下さい。
 *
 * ビヘイビアファイルの設定例:
 * <code>
 * validate:
 *   {validator_id}:
 *     # バリデータクラス名。
 *     class: PHI_EmojiValidator
 *
 *     # 絵文字が含まれる場合に通知するエラーメッセージ。
 *     matchError: {default_message}
 * </code>
 *
 * @link http://unicode.org/~scherer/emoji4unicode/snapshot/full.html Emoji Symbols: Background Data
 * @package validator
 */
class PHI_EmojiValidator extends PHI_Validator
{
  /**
   * 絵文字の正規表現パターン (UTF-8) を取得します。
   *
   * @return string 絵文字の正規表現パターンを返します。
   */
  public static function getRegexpPattern()
  {
    $path = sprintf('%s/vendors/unicode/EmojiSources.json', PHI_ROOT_DIR);
    $data = json_decode(file_get_contents($path), TRUE);
    $array = array();

    foreach ($data as $map) {
      $array[] = $map['utf8hex'];
    }

    $pattern = '/' . implode('|', $array) . ']/';

    return $pattern;
  }

  /**
   * 文字列内に絵文字が含まれていないかチェックします。
   *
   * @param string $value チェック対象の文字列。
   * @return bool 絵文字が含まれない場合に TRUE、含まれる場合は FALSE を返します。
   */
  public static function isValid($value)
  {
    $pattern = self::getRegexpPattern();

    if (preg_match($pattern, $value)) {
      return FALSE;
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
      $message = sprintf('Can not Emoji be used. [%s]', $fieldName);
    }

    $this->sendError($fieldName, $message);

    return FALSE;
  }
}
