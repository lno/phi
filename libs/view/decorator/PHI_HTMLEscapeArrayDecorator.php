<?php
/**
 * 配列に含まれる全てのデータ (スカラー、配列、オブジェクト) を HTML エスケープします。
 *
 * <code>
 * $array = array('&');
 * $decorator = new PHI_HTMLEscapeArrayDecorator($array);
 *
 * // &amp;
 * echo $decorator[0];
 *
 * $array = array('>' => '&');
 * $decorator = new PHI_HTMLEscapeArrayDecorator($array);
 *
 * foreach ($decorator as $key => $value) {
 *   // &gt;:&amp\n;
 *   echo $key . ':' . $value . "\n";
 * }
 * </code>
 *
 * @package view.decorator
 */
class PHI_HTMLEscapeArrayDecorator extends PHI_HTMLEscapeDecorator implements ArrayAccess, Iterator, Countable
{
  /**
   * @var array
   */
  private $_current;

  /**
   * コンストラクタ。
   *
   * @param array $array 対象とする配列データ。
   */
  public function __construct(array $array)
  {
    $this->_data = $array;
  }

  /**
   * @see ArrayAccess::offsetSet()
   */
  public function offsetSet($offset, $value)
  {
    if ($offset === NULL) {
      $this->_data[] = $value;
    } else {
      $this->_data[$offset] = $value;
    }
  }

  /**
   * @see ArrayAccess::offsetExists()
   */
  public function offsetExists($offset)
  {
    return isset($this->_data[$offset]);
  }

  /**
   * @see ArrayAccess::offsetUnset()
   */
  public function offsetUnset($offset)
  {
    unset($this->_data[$offset]);
  }

  /**
   * @see ArrayAccess::offsetGet()
   */
  public function offsetGet($offset)
  {
    if (isset($this->_data[$offset])) {
      return PHI_StringUtils::escape($this->_data[$offset]);
    }

    return NULL;
  }

  /**
   * @see Iterator::rewind()
   */
  public function rewind() {
    reset($this->_data);
  }

  /**
   * @see Iterator::current()
   */
  public function current() {
    return PHI_StringUtils::escape($this->_current);
  }

  /**
   * @see Iterator::key()
   */
  public function key() {
    return PHI_StringUtils::escape(key($this->_data));
  }

  /**
   * @see Iterator::next()
   */
  public function next() {
    next($this->_data);
  }

  /**
   * @see Iterator::valid()
   */
  function valid() {
    $current = current($this->_data);

    if ($current !== FALSE) {
      $this->_current = $current;

      return TRUE;
    }

    return FALSE;
  }

  /**
   * @see Countable::count()
   */
  public function count()
  {
    return sizeof($this->_data);
  }
}
