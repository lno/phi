<?php
/**
 * MIME タイプを判別するユーティリティメソッドを提供します。
 *
 * @package net.mail
 */
class PHI_MIME extends PHI_Object
{
  /**
   * 指定された MIME 文字列に含まれる MIME パートやパラメータを解析します。
   * <code>
   * // array(
   * //   'raw' => 'text/html; charset=utf-8',
   * //   'content' => 'text/html',
   * //   'type' => 'text',
   * //   'subtype' => 'html',
   * //   'parameters' => array('charset' => 'utf-8')
   * // );
   * PHI_MIME::parse('text/html; charset=utf-8');
   * </code>
   *
   * @param string $value 解析対象の MIME 文字列。
   * @return array 解析結果を返します。
   *   - raw: オリジナル文字列
   *   - content: フル形式の MIME 文字列 (パラメータ文字列を除く)
   *   - type: MIME パート文字列
   *   - subtype: サブ MIME パート文字列
   *   - parameters: MIME パラメータ配列
   */
  public static function parse($value)
  {
    $array = array();
    $array['raw'] = $value;

    $split = explode(';', $value);
    $pos = strpos($split[0], '/');

    if ($pos !== FALSE) {
      $array['content'] = $split[0];
      $array['type'] = substr($split[0], 0, $pos);
      $array['subtype'] = substr($split[0], $pos + 1);

    } else {
      $array['content'] = NULL;
      $array['type'] = NULL;
      $array['subtype'] = NULL;
    }

    $j = sizeof($split);

    if ($j > 1) {
      $parameters = array();

      for ($i = 1; $i < $j; $i++) {
        $parameter = explode('=', $split[$i]);

        if (isset($parameter[1])) {
          $parameters[$parameter[0]] = $parameter[1];
        }
      }

      $array['parameters'] = $parameters;
    }

    return $array;
  }

  /**
   * 指定した拡張子に対応する MIME タイプを取得します。
   *
   * @param string $extension 拡張子文字列。
   * @return array 拡張子に対応する MIME タイプリストを返します。
   */
  public static function getMimeTypes($extension)
  {
    $mimes = self::getExtensionMimeMappings();
    $extension = strtolower($extension);

    if (isset($mimes[$extension])) {
      $types = $mimes[$extension];

      if (!is_array($types)) {
//        $types = array($mimeTypes);
        $types = array($types);
      }

      return $types;
    }

    return FALSE;
  }

  /**
   * PHI_MIME クラスがサポートする MIME タイプと拡張子のマッピングテーブルを取得します。
   *
   * @return array MIME タイプと拡張子のマッピングテーブルを返します。
   */
  public static function getExtensionMimeMappings()
  {
    static $mimes = NULL;

    if ($mimes === NULL) {
      require PHI_ROOT_DIR . '/vendors/mimes.php';
    }

    return $mimes;
  }
}
