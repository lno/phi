<?php
/**
 * フォームから送信された 2 つのフィールドの文字列を比較します。
 *
 * ビヘイビアファイルの設定例:
 * <code>
 * validate:
 *   {validator_id}:
 *     # バリデータクラス名。
 *     class: PHI_CompareValidator
 *
 *     # 比較対象フィールド 1。
 *     compareField1:
 *
 *     # 比較対象フィールド 2。
 *     compareField2:
 *
 *     # フィールドの比較パターン。
 *     #   - match: 'compareField1' と 'compareField2' の値が同じであるかどうか。
 *     #            文字列の大文字・小文字は区別される。
 *     #   - lessThan: 'compareField1' の値は 'compareField2' の値未満であるかどうか。
 *     #               大文字・小文字の区別はせず、自然順アルゴリズムによる比較を行う。
 *     #   - moreThan: 'compareField1' の値は 'compareField2' の値より大きいかどうか。
 *     #                大文字・小文字の区別はせず、自然順アルゴリズムによる比較を行う。
 *     pattern: match
 *
 *     # フィールドの内容が 'pattern' で指定した条件にマッチしない場合に通知するエラーメッセージ。
 *     matchError: {default_message}
 *
 *     # 'compareField1' >= 'compareField2' の場合に通知するエラーメッセージ。
 *     lessThanError: {default_message}
 *
 *     # 'compareField1' <= 'compareField2' の場合に通知するエラーメッセージ。
 *     moreThanError: {default_message}
 * </code>
 *
 * @package validator
 */
class PHI_CompareValidator extends PHI_Validator
{
  /**
   * @throws PHI_ConfigurationException 必須属性がビヘイビアに定義されていない場合に発生。
   * @see PHI_Validator::validate()
   */
  public function validate($fieldName, $value, array $variables = array())
  {
    $holder = $this->buildParameterHolder($variables);
    $form = PHI_ActionForm::getInstance();

    $fieldName1 = $holder->getString('compareField1');
    $fieldValue1 = $form->get($fieldName1);

    $fieldName2 = $holder->getString('compareField2');
    $fieldValue2 = $form->get($fieldName2);
    $pattern = $holder->getString('pattern');

    if (empty($fieldName1)) {
      $message = sprintf('\'compareField1\' validator attribute is undefined.');
      throw new PHI_ConfigurationException($message);

    } else if (empty($fieldName2)) {
      $message = sprintf('\'compareField2\' validator attribute is undefined.');
      throw new PHI_ConfigurationException($message);
    }

    $message = NULL;

    switch ($pattern) {
      case 'moreThan':
        if (strnatcasecmp($fieldValue1, $fieldValue2) == 1) {
          return TRUE;
        }

        $message = $holder->getString('moreThanError', $message);

        if ($message === NULL) {
          $message = sprintf('%s is smaller than the value of %s.', $fieldName2, $fieldName1);
        }

        break;

      case 'lessThan':
        if (strnatcasecmp($fieldValue1, $fieldValue2) == -1) {
          return TRUE;
        }

        $message = $holder->getString('lessThanError', $message);

        if ($message === NULL) {
          $message = sprintf('%s is smaller than the value of %s.', $fieldName1, $fieldName2);
        }

        break;

      default:
        if (strcmp($fieldValue1, $fieldValue2) == 0) {
          return TRUE;
        }

        $message = $holder->getString('matchError');

        if ($message === NULL) {
          $message = sprintf('Values of a comparison item are different. [%s]', $fieldName);
        }

        break;
    }

    $this->sendError($fieldName, $message);

    return FALSE;
  }
}
