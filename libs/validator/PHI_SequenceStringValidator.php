<?php
/**
 * 同じ文字列の連続性を検証します。
 *
 * ビヘイビアファイルの設定例:
 * <code>
 * validate:
 *   {validator_id}:
 *     # バリデータクラス名。
 *     class: PHI_SequenceStringValidator
 *
 *     # 連続した文字をエラーと見なす文字数。
 *     size:
 *
 *     # マルチバイト文字を対象とするかどうか。
 *     multibyte: FALSE
 *
 *     # 同じ文字が 'size' 以上連続した場合に通知するエラーメッセージ。
 *     sizeError: {default_message}
 * </code>
 * ※'size' 属性の指定は必須です。
 *
 * @package validator
 */
class PHI_SequenceStringValidator extends PHI_Validator
{
  /**
   * 文字列 value に連続した文字が含まれるかどうかチェックします。
   *
   * @param string $value チェック対象の文字列。
   * @param int $size 連続した文字をエラーと見なす文字数。
   * @param bool $multibyte マルチバイト文字を対象とするか。
   * @return bool 連続した文字が含まれない (size 以下の) 場合に TRUE、含まれる場合に FALSE を返します。
   */
  public static function isValid($value, $size, $multibyte = FALSE)
  {
    $regexpOption = NULL;

    if ($multibyte) {
      if (mb_detect_encoding($value) != 'UTF-8') {
        $fromEncoding = PHI_Config::getApplication()->get('charset.default');
        $value = mb_convert_encoding($value, 'UTF-8', $fromEncoding);
      }

      $regexpOption = 'u';
    }

    $regexp = sprintf('/(.)\1{%s,}/%s', $size - 1, $regexpOption);

    if (preg_match($regexp, $value)) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * @throws PHI_ConfigurationException 必須属性がビヘイビアに定義されていない場合に発生。
   * @see PHI_Validator::validate()
   */
  public function validate($fieldName, $value, array $variables = array())
  {
    if (strlen($value) == 0) {
      return TRUE;
    }
    $holder = $this->buildParameterHolder($variables); // TODO TEST

    $size = $holder->getInt('size');
    $multibyte = $holder->getBoolean('multibyte');

    if ($size === NULL) {
      $message = 'Undefined \'size\' attribute.';
      throw new PHI_ConfigurationException($message);
    }

    if ($this->isValid($value, $size, $multibyte)) {
      return TRUE;
    }

    $message = $holder->getString('sizeError');

    if ($message === NULL) {
      $message = sprintf('Contains consecutive letters at %s characters.', $size);
    }

    $this->sendError($fieldName, $message);

    return FALSE;
  }
}
