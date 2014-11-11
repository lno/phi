<?php
/**
 * フォームから送信された文字列が数値として正当なものであるかどうかを検証します。
 *
 * ビヘイビアファイルの設定例:
 * <code>
 * validate:
 *   {validator_id}:
 *     # バリデータクラス名。
 *     class: PHI_NumericValidator
 *
 *     # 小数点以下の入力を許可する場合は TRUE を指定。
 *     float: FLASE
 *
 *     # 許可されない文字が含まれた場合に通知するエラーメッセージ。
 *     matchError: {default_message}
 * </code>
 *
 * @package validator
 */
class PHI_NumericValidator extends PHI_Validator
{
  /**
   * 数値の書式が正当なものであるかチェックします。
   *
   * @param string $value チェック対象の数値。
   * @param bool $float 小数点以下の入力を許可する場合は TRUE。
   * @return bool 数値の書式が正当なものかどうかを TRUE/FALSE で返します。
   */
  public static function isValid($value, $float = FALSE)
  {
    if (is_numeric($value) && ($float || (!$float && strpos($value, '.') === FALSE))) {
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
    $float = $holder->getBoolean('float');

    if (strlen($value) == 0 || self::isValid($value, $float)) {
      return TRUE;
    }

    $message = $holder->getString('matchError');

    if ($message === NULL) {
      $message = sprintf('Numeric format is illegal. [%s]', $fieldName);
    }

    $this->sendError($fieldName, $message);

    return FALSE;
  }
}
