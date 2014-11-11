<?php
/**
 * 郵便番号が日本式のフォーマットとして正しいかどうか検証します。
 *
 * ビヘイビアファイルの設定例:
 * <code>
 * validate:
 *   {validator_id}:
 *     # バリデータクラス名。
 *     class: PHI_ZipCodeValidator
 *
 *     # 対象とする国。(現在は 'jp' のみ対応)
 *     country: jp
 *
 *     # フィールド 1
 *     number1:
 *
 *     # フィールド 2
 *     number2:
 *
 *     # ハイフンを許可するか ('number*' を指定した場合は無効)
 *     hyphnate: TRUE
 *
 *     # 郵便番号の書式が不正な場合に通知するメッセージ。
 *     zipCodeError: {default_message}
 * </code>
 * o 'number*' が未指定の場合は、{validator_id} フィールドを用いた検証が実行されます。
 *
 * @package validator
 */
class PHI_ZipCodeValidator extends PHI_Validator
{
  /**
   * 国別のハイフンを含む郵便番号パターン、ハイフンを含まない郵便番号パターンリスト。
   * @var array
   */
  protected static $_patterns = array(
    'jp' => array('/^\d{3}-\d{4}$/', '/^\d{7}$/', )
  );

  /**
   * 郵便番号の形式が正当なものであるかどうかチェックします。
   *
   * @param string $value チェック対象の郵便番号。
   * @param bool $hyphnate ハイフンを許可するかどうか。
   * @return bool 郵便番号の形式が正当なものかどうかを TRUE/FALSE で返します。
   */
  public static function isValid($value, $hyphnate = TRUE)
  {
    if ($hyphnate) {
      $pattern = self::$_patterns['jp'][0];
    } else {
      $pattern = self::$_patterns['jp'][1];
    }

    return preg_match($pattern, $value);
  }

  /**
   * @see PHI_Validator::validate()
   */
  public function validate($fieldName, $value, array $variables = array())
  {
    $form = PHI_ActionForm::getInstance();
    $holder = $this->buildParameterHolder($variables);

    $number1 = $form->get($holder->getString('number1'));
    $number2 = $form->get($holder->getString('number2'));
    $hyphnate = $holder->getBoolean('hyphnate', FALSE);

    if (strlen($number1 . $number2)) {
      $fullZipCode = sprintf('%s-%s', $number1, $number2);
      $hyphnate = TRUE;

    } else {
      $fullZipCode = $value;
    }

    if (!self::isValid($fullZipCode, $hyphnate)) {
      $message = $holder->get('zipCodeError');

      if ($message === NULL) {
        $message = sprintf('Format of zip code is invalid. [%s]', $fieldName);
      }

      $this->sendError($fieldName, $message);

      return FALSE;
    }

    return TRUE;
  }
}
