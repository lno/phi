<?php
/**
 * 配列をオブジェクトとしてアクセスするためのユーティリティクラスです。
 *
 * <code>
 * $array = array(
 *   'database' => array(
 *     'default' => array(
 *       'dsn' => 'mysql:host=localhost; dbname=phi; port=3306',
 *       'user' => 'webapp',
 *       'password' => '...'
 *     )
 *   )
 * );
 *
 * $holder = new PHI_ParameterHolder($array);
 *
 * // 対象要素が配列の場合、{@link get()} メソッドは新しい PHI_ParameterHolder のインスタンスを返す
 * $databaseHolder = $holder->get('database');
 *
 * // オブジェクトを配列に変換
 * $databaseHolder->toArray();
 *
 * // '.' 区切りで要素にアクセスすることも可能
 * // 第二引数には値が NULL の場合に返す代替値を指定することもできる
 * $databaseHolder->get('default.dsn', 'default');
 *
 * // メソッドチェーン形式のアクセス
 * $holder->get('database')->get('default')->get('dsn');
 *
 * // 配列形式のアクセス
 * $databaseHolder['default'];
 *
 * // foreach() で全ての値を取得する
 * foreach ($databaseConfig as $name => $value) {
 *   ...
 * }
 *
 * // 要素数を取得する
 * $databaseHolder->count();
 * </code>
 *
 * @package util
 */
class PHI_ParameterHolder extends PHI_Object implements Iterator, ArrayAccess, Countable
{
  /**
   * パラメータリスト。
   * @var array
   */
  private $_array = array();

  /**
   * @var bool
   */
  private $_returnNewObject;

  /**
   * コンストラクタ。
   *
   * @param array $array 元となる配列データ。
   * @param bool $returnNewObject TRUE を指定することで、{@get()} メソッドの戻り値が配列の場合に、新しい PHI_ParameterHolder オブジェクトとして値を返します。
   */
  public function __construct(array $array = array(), $returnNewObject = FALSE)
  {
    $this->_array = $array;
    $this->_returnNewObject = $returnNewObject;
  }

  /**
   * @see Iterator::rewrind()
   */
  public function rewind()
  {
    reset($this->_array);
  }

  /**
   * @see Iterator::current()
   */
  public function current()
  {
    $current = current($this->_array);

    if ($this->_returnNewObject && is_array($current)) {
      $data = new PHI_ParameterHolder($current, $this->_returnNewObject);
    } else {
      $data = $current;
    }

    return $data;
  }

  /**
   * @see Iterator::key()
   */
  public function key()
  {
    return key($this->_array);
  }

  /**
   * @see Iterator::next()
   */
  public function next()
  {
    return next($this->_array);
  }

  /**
   * @see Iterator::valid()
   */
  public function valid()
  {
    if ($this->key() !== NULL) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * @see Countable::count()
   */
  public function count()
  {
    return sizeof($this->_array);
  }

  /**
   * @see ArrayAccess::offsetExists()
   */
  public function offsetExists($offset)
  {
    return array_key_exists($offset, $this->_array);
  }

  /**
   * @see ArrayAccess::offsetGet()
   */
  public function offsetGet($offset)
  {
    $result = NULL;

    if (isset($this->_array[$offset])) {
      $result = $this->_array[$offset];
    }

    return $result;
  }

  /**
   * @see ArrayAccess::offsetSet()
   */
  public function offsetSet($offset, $value)
  {
    $this->_array[$offset] = $value;
  }

  /**
   * @see ArrayAccess::offsetUnset()
   */
  public function offsetUnset($offset)
  {
    unset($this->_array[$offset]);
  }

  /**
   * パラメータホルダに設定されているデータの名前リストを取得します。
   *
   * @return array パラメータホルダに設定されているデータの名前リストを返します。
   */
  public function getNames()
  {
    return array_keys($this->_array);
  }

  /**
   * パラメータホルダに設定されているデータの値リストを取得します。
   *
   * @return array パラメータホルダに設定されているデータの値リストを返します。
   */
  public function getValues()
  {
    return array_values($this->_array);
  }

  /**
   * パラメータホルダに配列データを追加します。
   * 既に設定されている全ての値が上書きされる点に注意して下さい。
   *
   * @param array $parameters パラメータ配列。
   */
  public function setArray(array $array)
  {
    $this->_array = $array;
  }

  /**
   * パラメータホルダに値を設定します。
   * <code>
   * $holder->set('foo.bar', 100);
   *
   * // 100
   * $holder->get('foo.bar');
   * </code>
   *
   * @param string $name 追加するキー名。'.' (ピリオド) 区切りのキー名が指定された場合は連想配列として認識されます。
   * @param mixed $value 追加する値。
   * @param bool $override 同名のキーが存在する時に値を上書きする場合は TRUE を指定。
   */
  public function set($name, $value, $override = TRUE)
  {
    if ($this->hasName($name)) {
      if ($override) {
        $this->remove($name);
        PHI_ArrayUtils::build($name, $value, $this->_array);
      }

    } else {
      PHI_ArrayUtils::build($name, $value, $this->_array);
    }
  }

  /**
   * パラメータホルダに配列を再帰的にマージします。
   *
   * @param array $array マージする配列。
   */
  public function merge(array $array)
  {
    $this->_array = PHI_ArrayUtils::merge($this->_array, $array);
  }

  /**
   * パラメータホルダに指定した名前のキーが設定されているかチェックします。
   *
   * @param string $name 対象とするキー名。
   * @return bool 対象とするキーが存在する場合は TRUE、存在しない場合は FALSE を返します。
   *   配列の構成が array('foo' => array('bar' => 'baz')) といった場合は、name に 'foo'、または 'foo.bar' を指定することで TRUE が返されます。
   */
  public function hasName($name)
  {
  	$result = FALSE;
    PHI_ArrayUtils::find($this->_array, $name, NULL, $result);

    return $result;
  }

  /**
   * パラメータホルダから指定したキーの要素を取得します。
   *
   * @param string $name 対象とするキー名。
   * @param mixed $alternative 値が存在しない場合に返す代替値。
   * @return mixed キーが持つ値を返します。
   *   このメソッドは値が配列で構成される場合に新しい {@link PHI_ParameterHolder} オブジェクト、スカラー値を持つ場合は実値を返す点に注意して下さい。
   *   データを特定の型で取得するには {@link getArray()} や {@link getString()} を利用するべきです。
   */
  public function get($name, $alternative = NULL)
  {
    $data = PHI_ArrayUtils::find($this->_array, $name, $alternative);

    if ($this->_returnNewObject && is_array($data)) {
      $data = new PHI_ParameterHolder($data, TRUE);
    }

    return $data;
  }

  /**
   * パラメータホルダから指定したキーの要素を数値型で取得します。
   * このメソッドは戻り値をオブジェクトに変換しない分、{@link get()} メソッドより高速に動作します。
   *
   * @param string $name 検索対象のキー名。
   * @param mixed $alternative 値が存在しない場合に返す代替値。
   */
  public function getInt($name, $alternative = NULL)
  {
    return (int) PHI_ArrayUtils::find($this->_array, $name, $alternative);
  }

  /**
   * パラメータホルダから指定したキーの要素を浮動小数点型で取得します。
   * このメソッドは戻り値をオブジェクトに変換しない分、{@link get()} メソッドより高速に動作します。
   *
   * @param string $name 検索対象のキー名。
   * @param mixed $alternative 値が存在しない場合に返す代替値。
   */
  public function getFloat($name, $alternative = NULL)
  {
    return (float) PHI_ArrayUtils::find($this->_array, $name, $alternative);
  }

  /**
   * パラメータホルダから指定したキーの要素を論理型で取得します。
   * このメソッドは戻り値をオブジェクトに変換しない分、{@link get()} メソッドより高速に動作します。
   *
   * @param string $name 検索対象のキー名。
   * @param mixed $alternative 値が存在しない場合に返す代替値。(TRUE か FALSE)
   */
  public function getBoolean($name, $alternative = FALSE)
  {
    return (boolean) PHI_ArrayUtils::find($this->_array, $name, $alternative);
  }

  /**
   * パラメータホルダから指定したキーの要素を文字列型で取得します。
   * このメソッドは戻り値をオブジェクトに変換しない分、{@link get()} メソッドより高速に動作します。
   *
   * @param string $name 検索対象のキー名。
   * @param mixed $alternative 値が存在しない場合に返す代替値。
   */
  public function getString($name, $alternative = NULL)
  {
    $value = PHI_ArrayUtils::find($this->_array, $name, $alternative);

    if ($value !== NULL) {
      $value = (string) $value;
    }

    return $value;
  }

  /**
   * パラメータホルダから指定したキーの要素を配列型で取得します。
   * このメソッドは戻り値をオブジェクトに変換しない分、{@link get()} メソッドより高速に動作します。
   *
   * @param string $name 検索対象のキー名。
   * @return array キーに対応する値を配列型で返します。値が見つからない場合は空の配列を返します。
   */
  public function getArray($name)
  {
    $value = $this->get($name);

    if ($value instanceof PHI_ParameterHolder) {
      $result = $value->_array;
    } else {
      $result = (array) $value;
    }

    return $result;
  }

  /**
   * パラメータホルダに設定されている配列を取得します。
   *
   * @return array パラメータホルダに設定されている配列を返します。
   */
  public function toArray()
  {
    return $this->_array;
  }

  /**
   * パラメータホルダから指定したキーの要素を削除します。
   *
   * @param string $name 削除対象のキー名。
   * @return bool 削除に成功した場合は TRUE、失敗した場合は FALSE を返します。
   */
  public function remove($name)
  {
    return PHI_ArrayUtils::removeKey($this->_array, $name);
  }

  /**
   * パラメータホルダに設定されている全ての要素を削除します。
   */
  public function clear()
  {
    $this->_array = array();
  }
}
