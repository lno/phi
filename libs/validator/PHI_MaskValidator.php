<?php
/**
 * フォームから送信された文字列の正当性を正規表現パターンによって検証します。
 *
 * ビヘイビアファイルの設定例:
 * <code>
 * validate:
 *   {validator_id}:
 *     # バリデータクラス名。
 *     class: PHI_MaskValidator
 *
 *     # 正規表現のパターン。'/.../' 形式で指定。
 *     mask:
 *
 *     # 対象フィールドが正規表現パターンと一致しない場合に通知するエラーメッセージ。
 *     matchError: {default_message}
 * </code>
 *
 * @package validator
 */
class PHI_MaskValidator extends PHI_Validator
{
  /**
   * @throws PHI_ConfigurationException 必須属性がビヘイビアに定義されていない場合に発生。
   * @see PHI_Validator::validate()
   */
  public function validate($fieldName, $value, array $variables = array())
  {
    $holder = $this->buildParameterHolder($variables);

    if (strlen($value) == 0) {
      return TRUE;
    }

    $mask = $holder->getString('mask');

    if ($mask === NULL) {
      $message = sprintf('\'mask\' validator attribute is undefined.');
      throw new PHI_ConfigurationException($message);
    }

    if (preg_match($mask, $value)) {
      return TRUE;
    }

    $message = $holder->getString('matchError');

    if ($message === NULL) {
      $message = sprintf('Pattern is not matched. [%s]', $fieldName);
    }

    $this->sendError($fieldName, $message);

    return FALSE;
  }
}
