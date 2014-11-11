<?php
/**
 * IP アドレスのフォーマットが正当なものであるか検証します。
 *
 * ビヘイビアファイルの設定例:
 * <code>
 * validate:
 *   {validator_id}:
 *     # バリデータクラス名。
 *     class: PHI_IPAddressValidator
 *
 *     # IP アドレスのフォーマットが不正な場合に通知するエラーメッセージ。
 *     matchError: {default_message}
 * </code>
 *
 * @package validator
 */
class PHI_IPAddressValidator extends PHI_Validator
{
  /**
   * IP アドレスの書式が正当なものであるかチェックします。
   *
   * @param string $value チェック対象の IP アドレス。
   * @return bool IP アドレスの書式が正当なものかどうかを TRUE/FALSE で返します。
   */
  public static function isValid($value)
  {
    $verify = long2ip(ip2long($value));

    if ($value === $verify) {
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

    if (strlen($value) == 0 || self::isValid($value)) {
      return TRUE;
    }

    $message = $holder->getString('matchError');

    if ($message === NULL) {
      $message = sprintf('IP Address format is illegal. [%s]', $fieldName);
    }

    $this->sendError($fieldName, $message);

    return FALSE;
  }
}
