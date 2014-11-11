<?php
/**
 * URL のフォーマットが正当なものであるかどうかを検証します。
 *
 * ビヘイビアファイルの設定例:
 * <code>
 * validate:
 *   {validator_id}:
 *     # バリデータクラス名。
 *     class: PHI_URLValidator
 *
 *     # URL に含まれるクエリパラメータを許可する場合は TRUE を指定。
 *     query: FALSE
 *
 *     # URL のフォーマットが不正な場合に通知するエラーメッセージ。
 *     matchError: {default_message}
 * </code>
 *
 * @link http://www.din.or.jp/~ohzaki/perl.htm URI(URL) の正規表現
 * @package validator
 */
class PHI_URLValidator extends PHI_Validator
{
  /**
   * URL の正規表現パターン。
   */
  const URL_PATTERN = '/^(https?:\/\/[-_.!~*\'()a-zA-Z0-9;\/]+)$/';

  /**
   * クエリパラメータを含む URL の正規表現パターン。
   */
  const URL_QUERY_PATTERN = '/^(https?:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)$/';

  /**
   * URL の書式が正当なものであるかチェックします。
   *
   * @param string $value チェック対象の URL。
   * @param bool $query クエリパラメータを許可する場合は TRUE、許可しない場合は FALSE。規定値は FALSE。
   * @return bool URL の書式が正当なものかどうかを TRUE/FALSE で返します。
   */
  public static function isValid($value, $query = FALSE)
  {
    if ($query) {
      $mask = self::URL_QUERY_PATTERN;
    } else {
      $mask = self::URL_PATTERN;
    }

    if (preg_match($mask, $value)) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * @see PHI_Validator::validate()
   */
  public function validate($fieldName, $value, array $variables = array())
  {
    $holder = $this->buildParameterHolder($variables);
    $query = $holder->getBoolean('query');

    if (strlen($value) == 0 || self::isValid($value, $query)) {
      return TRUE;
    }

    $message = $holder->getString('matchError');

    if ($message === NULL) {
      $message = sprintf('URL format is illegal. [%s]', $fieldName);
    }

    $this->sendError($fieldName, $message);

    return FALSE;
  }
}
