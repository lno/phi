<?php
/**
 * @package rc.util
 */
class ArrayUtil
{
  public static function get(array $list, $alt = [])
  {
    return ArrayUtil::isEmpty($list) ? $alt : $list;
  }

  public static function isEmpty(array $list)
  {
    $result = TRUE;

    // TODO PHI_HTMLEscapeArrayDecorator ラッパーの自動サニタイズを検証
    if (is_a($list, 'PHI_HTMLEscapeArrayDecorator')) {
      $list = $list->getRaw();
    }

    if (!empty($list)) {
      foreach ($list as $item) {
        if (is_array($item)) {
          if (ArrayUtil::exists($item)) {
            $result = FALSE;
            break;
          }
        } else {
          if (StringUtil::exists($item)) {
            $result = FALSE;
            break;
          }
        }
      }
    }

    return $result;
  }

  public static function exists(array $list)
  {
    return !ArrayUtil::isEmpty($list);
  }

  /**
   * 型までチェックした上で値が配列に含まれるかをチェックする
   * ※ $list に 0 や 1 が入っている場合は要注意
   *
   * @param array $list
   * @param string|array $search
   * @param bool $orSearch
   * @return bool
   */
  public static function has(array $list, $search, $orSearch = TRUE)
  {
    if (is_array($search)) {
      if ($orSearch) {
        $result = FALSE;
        foreach ($search as $word) {
          if (ArrayUtil::has($list, $word, $orSearch)) {
            $result = TRUE;
            break;
          }
        }
      } else {
        $result = TRUE;
        foreach ($search as $word) {
          if (!ArrayUtil::has($list, $word, $orSearch)) {
            $result = FALSE;
            break;
          }
        }
      }
      return $result;
    } else {
      // search を 文字列として処理
      return in_array($search, $list, TRUE);
    }
  }

  /**
   * @param array $list
   * @param $key
   * @return bool
   */
  public static function hasKey(array $list, $key)
  {
    return array_key_exists($key, $list);
  }

  public static function join(array $list, $delimiter = '')
  {
    return implode($delimiter, $list);
  }

  public static function removeItem(array &$list, $value)
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

  public static function merge($list1, $list2)
  {
    if (!ArrayUtil::isEmpty($list1) && !ArrayUtil::isEmpty($list2)) {
      return array_merge($list1, $list2);
    } else if (!ArrayUtil::isEmpty($list1) && ArrayUtil::isEmpty($list2)) {
      return $list1;
    } else if (ArrayUtil::isEmpty($list1) && !ArrayUtil::isEmpty($list2)) {
      return $list2;
    } else {
      return [];
    }
  }
}