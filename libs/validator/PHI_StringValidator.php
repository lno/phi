<?php
/**
 * フォームから送信された文字列が英数字で構成されているか検証します。
 *
 * ビヘイビアファイルの設定例:
 * <code>
 * validate:
 *   {validator_id}:
 *     # バリデータクラス名。
 *     class: PHI_StringValidator
 *
 *     # 文字列が英字で構成されているかどうかを検証する。
 *     alphabet: FALSE
 *
 *     # 文字列が数値で構成されているかどうかを検証する。
 *     numeric: FALSE
 *
 *     # 文字列に許可されない文字が含まれる場合に通知するエラーメッセージ。
 *     matchError: {default_message}
 * </code>
 * ※'alphabet'、'numeric' の両方が TRUE の場合、文字列が英数字で構成されているかどうかをチェックします。
 *
 * @package validator
 */
class PHI_StringValidator extends PHI_Validator
{
  /**
   * @see PHI_Validator::validate()
   */
  public function validate($fieldName, $value, array $variables = array())
  {
    $holder = $this->buildParameterHolder($variables);

    if (strlen($value) == 0) {
      return TRUE;
    }

    $alphabet = $holder->getBoolean('alphabet', TRUE);
    $numeric = $holder->getBoolean('numeric', TRUE);

    if ($alphabet) {
      if ($numeric) {
        if (ctype_alnum($value)) {
          return TRUE;
        }

      } else if (ctype_alpha($value)) {
        return TRUE;
      }

    } else if (ctype_digit($value)) {
      return TRUE;
    }

    $message = $holder->getString('matchError');

    if ($message === NULL) {
      $message = sprintf('String format is illegal. [%s]', $fieldName);
    }

    $this->sendError($fieldName, $message);

    return FALSE;
  }
}
