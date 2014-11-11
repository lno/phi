<?php
/**
 * eAccelerator によるキャッシュ管理機能を提供します。
 * この機能を利用するには、eAccelerator インストール時に '--with-eaccelerator-shared-memory' オプションが有効化されている必要があります。
 *
 * @link http://eaccelerator.net/ eAccelerator
 * @package cache
 */
class PHI_EAcceleratorCache extends PHI_Cache
{
  /**
   * @param string $namespace キャッシュを格納する名前空間の指定。'foo.bar' のように '.' (ドット) で階層化することが出来ます。
   * @param int $expire キャッシュの有効期限秒。未指定時はキャッシュが削除されるか無効 (キャッシュストレージの再起動など) になるまで値を持続します。
   * @see PHI_Cache::set()
   */
  public function set($name, $value, $namespace = NULL, $expire = PHI_Cache::EXPIRE_UNLIMITED)
  {
    $key = $this->getCachePath($name, $namespace);
    $array = array($value, $_SERVER['REQUEST_TIME']);

    return eaccelerator_put($key, $array, $expire);
  }

  /**
   * @param string $namespace キャッシュを格納する名前空間の指定。
   * @see PHI_Cache::get()
   */
  public function get($name, $namespace = NULL)
  {
    $key = $this->getCachePath($name, $namespace);
    $array = eaccelerator_get($key);

    if ($array !== FALSE) {
      return $array[0];
    }

    return NULL;
  }

  /**
   * @param string $namespace キャッシュを格納する名前空間の指定。
   * @see PHI_Cache::hasCached()
   */
  public function hasCached($name, $namespace = NULL)
  {
    $key = $this->getCachePath($name, $namespace);
    $array = eaccelerator_get($key);

    if ($array !== FALSE) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * @param string $namespace キャッシュを格納する名前空間の指定。
   * @see PHI_Cache::delete()
   */
  public function delete($name, $namespace = NULL)
  {
    $name = $this->getCachePath($name, $namespace);

    return eaccelerator_rm($name);
  }

  /**
   * @see PHI_Cache::clear()
   */
  public function clear()
  {
    eaccelerator_gc();

    return TRUE;
  }

  /**
   * @param string $namespace キャッシュを格納する名前空間の指定。
   * @see PHI_Cache::getExpire()
   */
  public function getExpire($name, $namespace = NULL)
  {
    $key = $this->getCachePath($name, $namespace);
    $array = eaccelerator_get($key);

    if ($array !== FALSE) {
      return $array[1];
    }

    return NULL;
  }
}
