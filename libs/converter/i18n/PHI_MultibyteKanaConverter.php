<?php
/**
 * マルチバイト文字列中の全角・半角を変換します。
 * このクラスは日本語環境でのみ使用可能です。
 *
 * ビヘイビアファイルの設定例:
 * <code>
 * convert:
 *   {convert_id}:
 *     names: {field_name,...}
 *     converters:
 *       # コンバータクラス名。
 *       - class: PHI_MultibyteKanaConverter
 *
 *         # 変換タイプの指定。
 *         #   - full: 半角英数字、半角スペース、半角カタカナを全角文字に変換。
 *         #   - half: 全角英数字、全角スペースを半角文字に変換。(全角カタカナは半角カタカナに変換しない)
 *         #   - katakana: 全角ひらがなを全角カタカナに変換。
 *         #   - kana: 全角カタカナ、半角カタカナを全角ひらがなに変換。
 *         type:
 *
 *         # カナ変換オプション。{@link mb_convert_kana()} の $option に渡す引数。
 *         custom: {string}
 * </code>
 *
 * ※'type'、'custom' のいずれかの指定が必須です。
 *
 * @package converter.i18n
 */
class PHI_MultibyteKanaConverter extends PHI_Converter
{
  /**
   * 内部エンコーディング。
   * @var string
   */
  private $_internalEncoding;

  /**
   * @var string
   */
  private $_type;

  /**
   * @var string
   */
  private $_custom;

  /**
   * @see PHI_Converter::__construct()
   */
  public function __construct($converterId, PHI_ParameterHolder $holder)
  {
    parent::__construct($converterId, $holder);

    $this->_internalEncoding = PHI_Config::getApplication()->get('charset.default');
    $this->_type = $holder->getString('type');
    $this->_custom = $holder->getString('custom');
  }

  /**
   * @see PHI_Converter::convert()
   */
  public function convert($string)
  {
    $option = NULL;

    if ($this->_type !== NULL) {
      switch ($this->_type) {
        case 'full':
          $option = 'ASK';
          break;

        case 'half':
          $option = 'as';
          break;

        case 'katakana':
          $option = 'C';
          break;

        case 'kana':
          $option = 'cH';
          break;
      }

    } else if ($this->_custom !== NULL) {
      $option = $this->_custom;
    }

    if ($option) {
      $string = mb_convert_kana($string, $option, $this->_internalEncoding);
    }

    return $string;
  }
}
