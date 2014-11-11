<?php
/**
 * XCache によるキャッシュ管理機能を提供します。
 * この機能を利用するには、実行環境において XCache がインストールされている必要があります。
 *
 * @link http://xcache.lighttpd.net/ XCache
 * @package cache
 */
class PHI_XCacheCache extends PHI_Cache
{
  /**
   * コンストラクタ。
   *
   * @param array $options XCache 設定オプション。
   *   - $options['user']: XCache の管理者 ID。
   *   - $options['password']: XCache の管理者パスワード。
   */
  public function __construct(array $options = array())
  {
    if (isset($options['user'])) {
      $_SERVER['PHP_AUTH_USER'] = $options['user'];
    }

    if (isset($options['password'])) {
      $_SERVER['PHP_AUTH_PW'] = $options['password'];
    }
  }

  /**
   * @param string $namespace キャッシュを格納する名前空間の指定。'foo.bar' のように '.' (ドット) で階層化することが出来ます。
   * @param int $expire キャッシュの有効期限秒。未指定時はキャッシュが削除されるか無効 (キャッシュストレージの再起動など) になるまで値を持続します。
   * @see PHI_Cache::set()
   */
  public function set($name, $value, $namespace = NULL, $expire = PHI_Cache::EXPIRE_UNLIMITED)
  {
    $key = $this->getCachePath($name, $namespace);
    $array = array($value, $_SERVER['REQUEST_TIME']);

    return xcache_set($key, $array, $expire);
  }

  /**
   * @param string $namespace キャッシュを格納する名前空間の指定。
   * @see PHI_Cache::get()
   */
  public function get($name, $namespace = NULL)
  {
    $key = $this->getCachePath($name, $namespace);
    $array = xcache_get($key);

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

    return xcache_isset($key);
  }

  /**
   * @param string $namespace キャッシュを格納する名前空間の指定。
   * @see PHI_Cache::delete()
   */
  public function delete($name, $namespace = NULL)
  {
    $key = $this->getCachePath($name, $namespace);

    return xcache_unset($key);
  }

  /**
   * @see PHI_Cache::clear()
   */
  public function clear()
  {
    $j = xcache_count(XC_TYPE_VAR);

    for ($i = 0; $i < $j; $i++) {
      xcache_clear_cache(XC_TYPE_VAR, $i);
    }

    return TRUE;
  }

  /**
   * @param string $namespace キャッシュを格納する名前空間の指定。
   * @see PHI_Cache::getExpire()
   */
  public function getExpire($name, $namespace = NULL)
  {
    $key = $this->getCachePath($name, $namespace);
    $array = xcache_get($key);

    if ($array !== FALSE) {
      return $array[1];
    }

    return NULL;
  }
}
