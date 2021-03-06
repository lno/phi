<?php
/**
 * APC (Alternative PHP Cache)  によるキャッシュ管理機能を提供します。
 * この機能を利用するには、実行環境において PECL APC パッケージがインストールされている必要があります。
 *
 * <i>
 *   CLI 上で APC を使うには、php.ini 上で 'apc.enable_cli' の値を 1 に設定しておく必要があります。
 *   'apc.enable_cli' のアクセスレベルは PHP_INI_SYSTEM のため、プログラム上から変更することはできません。
 * </i>
 *
 * @link http://pecl.php.net/package/apc PECL APC
 * @package cache
 */
class PHI_APCCache extends PHI_Cache
{
  /**
   * コンストラクタ。
   */
  public function __construct()
  {}

  /**
   * @param string $namespace キャッシュを格納する名前空間の指定。'foo.bar' のように '.' (ドット) で階層化することが出来ます。
   * @param int $expire キャッシュの有効期限秒。未指定時はキャッシュが削除されるか無効 (キャッシュストレージの再起動など) になるまで値を持続します。
   * @see PHI_Cache::set()
   */
  public function set($name, $value, $namespace = NULL, $expire = PHI_Cache::EXPIRE_UNLIMITED)
  {
    $key = $this->getCachePath($name, $namespace);
    $object = new ArrayObject(array($value, $_SERVER['REQUEST_TIME']));

    // apc_store() はデータのシリアライズが行われていない可能性がある
    // (配列要素のオブジェクトが取得できない場合があった)
    $object = serialize($object);

    return apc_store($key, $object, $expire);
  }

  /**
   * @param string $namespace キャッシュを格納する名前空間の指定。
   * @see PHI_Cache::get()
   */
  public function get($name, $namespace = NULL)
  {
    $key = $this->getCachePath($name, $namespace);
    $object = unserialize(apc_fetch($key));

    if ($object !== FALSE) {
      $array = $object->getArrayCopy();

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

    if (apc_fetch($key) !== FALSE) {
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
    $key = $this->getCachePath($name, $namespace);

    return apc_delete($key);
  }

  /**
   * @see PHI_Cache::clear()
   */
  public function clear()
  {
    return apc_clear_cache('user');
  }

  /**
   * @param string $namespace キャッシュを格納する名前空間の指定。
   * @see PHI_Cache::getExpire()
   */
  public function getExpire($name, $namespace = NULL)
  {
    $key = $this->getCachePath($name, $namespace);
    $object = unserialize(apc_fetch($key));

    if ($object !== FALSE) {
      return $object->getArrayCopy();
    }

    return NULL;
  }
}
