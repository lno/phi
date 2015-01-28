<?php
/**
 * @package rc.util
 */
class ArrayUtil
{
  /**
   * 型までチェックした上で値が配列に含まれるかをチェックする
   * ※ $list に 0 や 1 が入っている場合は要注意
   *
   * @param string|array $checkValue
   * @param array $list
   * @return bool
   */
  public static function hasValue(array $list, $checkValue)
  {
    return in_array($checkValue, $list, TRUE);
  }

  public static function merge($list1, $list2)
  {
    if (!ArrayUtil::isEmpty($list1) && !ArrayUtil::isEmpty($list2)) {
      return array_merge($list1, $list2);
    } else if (!ArrayUtil::isEmpty($list1) && ArrayUtil::isEmpty($list2)) {
      return $list1;
    } else if (ArrayUtil::isEmpty($list1) && !ArrayUtil::isEmpty($list2)) {
      return $list2;
    } else {
      return NULL;
    }
  }

  public static function join(array $list, $delimiter = '')
  {
    return implode($delimiter, $list);
  }

  public static function split(array $list, $delimiter)
  {
    return explode($delimiter, $list);
  }

  public static function isEmpty(array $list)
  {
    $result = TRUE;

    if (is_a($list, 'PHI_HTMLEscapeArrayDecorator')) {
      $list = $list->getRaw();
    }

    if (!empty($list)) {
      foreach ($list as $item) {
        if (!StringUtil::isEmpty($item)) {
          $result = FALSE;
        }
      }
    }

    return $result;
  }

  public static function exists(array $list)
  {
    return !ArrayUtil::isEmpty($list);
  }

  public static function deleteValue(array &$list, $value)
  {
    $deleteKeyList = [];

    foreach ($list as $key => $item) {
      if ($item == $value) {
        $deleteKeyList[] = $key;
      }
    }

    rsort($deleteKeyList);
    foreach ($deleteKeyList as $deleteKey) {
      unset($list[$deleteKey]);
    }
  }
}