<?php
/**
 * 複数のフィールドを結合し、指定したフォーマットに文字列変換を行います。
 *
 * ビヘイビアファイルの設定例:
 * <code>
 * convert:
 *   {convert_id}:
 *     names: {field_name,...}
 *     converters:
 *       # コンバータクラス名。
 *       - class: PHI_FormatConverter
 *
 *         # 変換する文字列の形式。使用可能なフォーマットは PHP の {@link sprintf()} 関数を参照。
 *         # フォーマットが不正な場合は {@link PHI_ParseException} が発生。
 *         format:
 *
 *         # 変換元となるフィールド名を配列表記で指定。
 *         arguments:
 *
 *         # フォーマット変換した値をセットするフィールド名。
 *         dest:
 * </code>
 * @package converter
 */
class PHI_FormatConverter extends PHI_Converter
{
  /**
   * @throws PHI_ParseException 'format' 属性の書式解析が失敗した場合に発生。
   * @see PHI_Converter::convert()
   */
  public function convert($string)
  {
    $format = $this->_holder->getString('format');
    $arguments = $this->_holder->getArray('arguments');
    $dest = $this->_holder->getString('dest');

    $form = PHI_ActionForm::getInstance();

    if (is_array($arguments)) {
      $fields = $form->getFields();
      $fieldValues[] = $format;

      foreach ($arguments as $fieldName) {
        $fieldValues[] = PHI_ArrayUtils::find($fields, $fieldName);
      }

      try {
        $result = call_user_func_array('sprintf', $fieldValues);
        $form->set($dest, $result);

      } catch (ErrorException $e) {
        $message = $e->getMessage();
        $message = substr($message, strpos($message, ':') + 2);
        $message = sprintf('%s [%s]', $message, $format);

        throw new PHI_ParseException($message);
      }
    }
  }
}
