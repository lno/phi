<?php
/**
 * フォームから送信された文字列が空文字でないかどうかを検証します。
 *
 * ビヘイビアファイルの設定例:
 * <code>
 * validate:
 *   {validator_id}:
 *     # バリデータクラス名。
 *     class: PHI_RequiredValidator
 *
 *     # 対象フィールドで空文字を許可するかどうか。
 *     required: FALSE
 *
 *     # 対象フィールドが空文字の場合に通知するエラーメッセージ。
 *     requiredError: {default_message}
 *
 *     # ホワイトスペース (\r、\t、\n、\f) で構成された文字列を許可するかどうか。
 *     whitespace: FALSE
 * </code>
 *
 * 空文字チェックは次のような記述形式もサポートしています。
 * <code>
 * validate:
 *   names:
 *    {フィールド名}:
 *      required:
 *      requiredError:
 * </code>
 * ※'whitespace' 属性に関しては、'validate:validators' 属性下でのみ使用可能です。
 *
 * @package validator
 */
class PHI_RequiredValidator extends PHI_Validator
{
  /**
   * 対象文字列内にホワイトスペースが含まれているかチェックします。
   * このメソッドはビヘイビアに定義された whitespace 属性が TRUE の場合のみ {@link validate()} メソッドからコールされます。
   *
   * @param string $value 対象となる文字列。
   * @return bool ホワイトスペースが含まれる場合は TRUE、含まれない場合は FALSE を返します。
   */
  public function hasWhitespace($value)
  {
    if (preg_match('/^[\s]+$/', $value)) {
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
    $required = $holder->getBoolean('required');

    if (!$required || is_array($value)) {
      return TRUE;

    } else if (strlen($value)) {
      if ($holder->getBoolean('whitespace')) {
        return TRUE;

      } else {
        if (!$this->hasWhitespace($value)) {
          return TRUE;
        }
      }
    }

    $message = $holder->getString('requiredError');

    if ($message === NULL) {
      $message = sprintf('Field is empty. [%s]', $fieldName);
    }

    $this->sendError($fieldName, $message);

    return FALSE;
  }
}
