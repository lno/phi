<?php
/**
 * 何も処理しないキャッシュクラスです。
 * このクラスは、開発環境でキャッシュの動作を無効にしたい場合に有効でしょう。
 *
 * @package cache
 */
class PHI_NullCache extends PHI_Cache
{
  /**
   * @return bool TRUE を返します。
   * @see PHI_Cache::set()
   */
  public function set($name, $value)
  {
    return TRUE;
  }

  /**
   * @return mixed NULL を返します。
   * @see PHI_Cache::get()
   */
  public function get($name)
  {
    return NULL;
  }

  /**
   * @return bool FALSE を返します。
   * @see PHI_Cache::hasCached()
   */
  public function hasCached($name)
  {
    return FALSE;
  }

  /**
   * @return bool TRUE を返します。
   * @see PHI_Cache::delete()
   */
  public function delete($name)
  {
    return TRUE;
  }

  /**
   * @return bool TRUE を返します。
   * @see PHI_Cache::clear()
   */
  public function clear()
  {
    return TRUE;
  }

  /**
   * @return int NULL を返します。
   * @see PHI_Cache::getExpire()
   */
  public function getExpire($name)
  {
    return NULL;
  }
}
