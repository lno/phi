<?php
/**
 * フォームから送信された内容に対し、ビヘイビアで設定したコンバートルールを適用するマネージャ機能を提供します。
 *
 * @package converter
 */
class PHI_ConvertManager extends PHI_Object
{
  /**
   * コンバート属性配列。
   * (global_behavior.yml、アクションビヘイビアの 'convert' 属性)
   * @var array
   */
  private $_convertConfig;

  /**
   * コンストラクタ。
   *
   * @param PHI_ParameterHolder $convertConfig コンバータ属性。
   */
  public function __construct(PHI_ParameterHolder $convertConfig)
  {
    $this->_convertConfig = $convertConfig;
  }

  /**
   * ビヘイビアに定義されているコンバータを適用します。
   */
  public function execute()
  {
    $convertConfig = $this->_convertConfig;
    $form = PHI_ActionForm::getInstance();

    foreach ($convertConfig as $converterId => $rules) {
      $fieldNames = NULL;

      if (isset($rules['names'])) {
        if ($rules['names'] === '@all') {
          $fieldNames = array_keys($form->getFields());
        } else {
          $fieldNames = explode(',', $rules['names']);
        }
      }

      foreach ($rules['converters'] as $index => $attributes) {
        $holder = new PHI_ParameterHolder($attributes);
        $className = $holder->getString('class');

        if (PHI_StringUtils::nullOrEmpty($className)) {
          $message = sprintf('"class" attribute is undefined. [%s]', $converterId);
          throw new PHI_ConfigurationException($message);
        }

        $converter = new $className($converterId, $holder);

        if ($fieldNames === NULL) {
          $converter->convert(NULL);

        } else {
          foreach ($fieldNames as $fieldName) {
            $fieldName = trim($fieldName);
            $fieldValue = $form->get($fieldName);

            if (is_array($fieldValue)) {
              $this->groupFieldToConvert($converter, $fieldValue, $fieldName);

            } else {
              $postValue = $converter->convert($fieldValue);
              $form->set($fieldName, $postValue);
            }
          }
        }
      }
    }
  }

  /**
   * @param PHI_Converter $converter
   * @param string $fieldValue
   * @param string $postFieldName
   */
  private function groupFieldToConvert(PHI_Converter $converter, $fieldValue, $postFieldName = NULL)
  {
    $form = PHI_ActionForm::getInstance();

    foreach ($fieldValue as $name => $value) {
      $name = $postFieldName . '.' . $name;

      if (is_array($value)) {
        $this->groupFieldToConvert($converter, $value, $name);

      } else {
        $postValue = $converter->convert($value);
        $form->set($name, $postValue);
      }
    }
  }
}
