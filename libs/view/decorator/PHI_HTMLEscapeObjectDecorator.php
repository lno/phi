<?php
/**
 * オブジェクトに含まれる全てのデータ (スカラー、配列、オブジェクト) を HTML エスケープします。
 *
 * <code>
 * class Foo
 * {
 *   public function getString()
 *   {
 *     return '&';
 *   }
 *
 *   public function getArray()
 *   {
 *     return array('<', '>');
 *   }
 * }
 *
 * $object = new Foo();
 * $decorator = new PHI_HTMLEscapeObjectDecorator($object);
 *
 * // &amp;
 * echo $decorator->getString();
 *
 * $array = $decorator->getArray();
 *
 * // '&lt'
 * echo $array[0];
 * </code>
 *
 * @package view.decorator
 */
class PHI_HTMLEscapeObjectDecorator extends PHI_HTMLEscapeDecorator implements ArrayAccess
{
  /**
   * コンストラクタ。
   *
   * @param object $object 対象とするオブジェクトデータ。
   */
  public function __construct($object)
  {
    $this->_data = $object;
  }

  /**
   * @see ArrayAccess::offsetExists()
   */
  public function offsetExists($name)
  {
    $result = FALSE;

    if (array_key_exists($name, $this->_data)) {
      $result = TRUE;
    }

    return $result;
  }

  /**
   * @see ArrayAccess::offsetGet()
   */
  public function offsetGet($name)
  {
    $result = NULL;

    if (isset($this->_data[$name])) {
      $result = $this->_data[$name];
    }

    return $result;
  }

  /**
   * @see ArrayAccess::offsetSet()
   */
  public function offsetSet($name, $value)
  {
    $this->_data[$name] = $value;
  }

  /**
   * @see ArrayAccess::offsetUnset()
   */
  public function offsetUnset($name)
  {
    unset($this->_data[$name]);
  }

  /**
   * 対象オブジェクトのプロパティをコールした際に実行されるメソッド。
   *
   * @param string $propertyName コールするプロパティ名。
   * @return mixed プロパティの値を返します。
   * @throws RuntimeException 指定されたプロパティが存在しない場合に発生。
   */
  public function __get($propertyName)
  {
    if (property_exists($this->_data, $propertyName)) {
      return PHI_StringUtils::escape($this->_data->$propertyName);

    } else {
      $message = sprintf('Property does not exist. [%s::$%s]', get_class($this->_data), $propertyName);
      throw new RuntimeException($message);
    }
  }

  /**
   * 対象オブジェクトのメソッドをコールした際に実行されるメソッド。
   *
   * @param string $methodName コールするメソッド名。
   * @param array $arguments メソッドに渡す引数のリスト。
   * @return mixed メソッドのコールバック結果を返します。
   * @throws RuntimeException 指定されたメソッドが存在しない場合に発生。
   */
  public function __call($methodName, array $arguments = array())
  {
    if (method_exists($this->_data, $methodName)) {
      $value = call_user_func_array(array($this->_data, $methodName), $arguments);

      return PHI_StringUtils::escape($value);

    } else {
      $message = sprintf('Method does not exist. [%s::%s()]', get_class($this->_data), $methodName);
      throw new RuntimeException($message);
    }
  }
}
