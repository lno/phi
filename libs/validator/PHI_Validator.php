<?php
/**
 * データを検証するためのメソッドを定義する抽象クラスです。
 *
 * @package validator
 */
abstract class PHI_Validator extends PHI_Object
{
  /**
   * @var string
   */
  protected $_validatorId;

  /**
   * @var PHI_ParameterHolder
   */
  protected $_holder;

  /**
   * @var PHI_ActionMessages
   */
  protected $_messages;

  /**
   * コンストラクタ。
   *
   * @param string $validatorId バリデータ ID。
   * @param PHI_ParameterHolder $holder パラメータホルダ。
   * @param PHI_ActionMessages $messages エラーメッセージを追加するメッセージオブジェクト。
   */
  public function __construct($validatorId, PHI_ParameterHolder $holder, PHI_ActionMessages $messages)
  {
    $this->_validatorId = $validatorId;
    $this->_holder = $holder;
    $this->_messages = $messages;
  }

  /**
   * バリデータ属性を構築します。
   *
   * @param array $variables バリデータに割り当てる変数のリスト。
   * @return PHI_ParameterHolder パラメータホルダオブジェクトを返します。
   */
  protected function buildParameterHolder(array $variables = array())
  {
    $holder = new PHI_ParameterHolder();

    foreach ($this->_holder as $attributeName => $attributeValue) {
      if ($attributeName == 'class') {
        $holder->set($attributeName, $attributeValue);

      } else {
        if (!is_array($attributeValue)) {
          // 文字列の展開
          if (preg_match('/{%\w+%}/', $attributeValue)) {
            foreach ($variables as $variableName => $variableValue) {
              if (!is_array($variableValue)) {
                $replace = '{%' . $variableName . '%}';
                $attributeValue = str_replace($replace, $variableValue, $attributeValue);
              }
            }

          // 変数の展開
          } else if (preg_match('/^\${(\w+)}$/', $attributeValue, $matches)) {
            if (isset($variables[$matches[1]])) {
              $attributeValue = $variables[$matches[1]];
            } else {
              $attributeValue = NULL;
            }
          }
        }

        if ($attributeValue !== NULL) {
          $holder->set($attributeName, $attributeValue);
        }

      } // end if
    } // end foreach

    return $holder;
  }

  /**
   * {@link validate()} メソッドで発生したエラーを {@link PHI_ActionMessages メッセージオブジェクト} に送信します。
   *
   * @param string $fieldName エラーを含むフィールド名。
   * @param string $message エラーメッセージ。
   */
  protected function sendError($fieldName, $message)
  {
    $this->_messages->addFieldError($fieldName, $message);
  }

  /**
   * データの検証を行います。
   * フィールドにエラーが含まれる場合、エラーメッセージは {@link PHI_ActionMessages メッセージオブジェクト} に送信されます。
   *
   * @param string $fieldName 検証するフィールド名。
   * @param string $value 検証するフィールドの値。
   * @param array $variables 差し込み変数のリスト。
   * @return bool データの検証に成功した場合は TRUE、失敗した場合は FALSE を返します。
   */
  abstract public function validate($fieldName, $value, array $variables = array());
}
