<?php
/**
 * 関数呼び出しをキャッシュします。
 *
 * @package cache
 */
class PHI_FunctionCache extends PHI_Object
{
  /**
   * {@link PHI_Cache} オブジェクト。(既定値は {@link PHI_CacheManager::CACHE_TYPE_FILE})
   * @var PHI_Cache
   */
  private $_cache;

  /**
   * コンストラクタ。
   *
   * @param int $cacheType キャッシュストレージの指定。PHI_CacheManager::CACHE_TYPE_* 定数を指定可能。
   */
  public function __construct($cacheType = PHI_CacheManager::CACHE_TYPE_FILE)
  {
    $this->_cache = PHI_CacheManager::getInstance($cacheType);
  }

  /**
   * 関数をコールします。
   *
   * @param string $function コールする関数。クラスメソッドを指定する場合は "array('Foo', 'bar')" のようになります。
   * @param array $arguments 関数に渡すパラメータの配列。
   * @return mixed 関数のコール結果を返します。
   * @throws ErrorException 存在しない関数をコールした際に発生。
   */
  public function call($function, array $arguments = array())
  {
    if (!is_callable($function)) {
      if (is_array($function)) {
        $name = sprintf('%s#%s', get_class($function[0]), $function[1]);
      } else {
        $name = $function;
      }

      $message = sprintf('Does not exist method. [%s]', $name);
      throw new ErrorException($message);
    }

    $namespace = 'function.' . md5($function);
    $key = md5($arguments);

    if ($this->_cache->hasCached($key, $namespace)) {
      $data = $this->_cache->get($key, $namespace);

    } else {
      $data = array();

      ob_start();

      // CLI 環境では自動フラッシュが有効だと性能が低下するため OFF にしておく
      ob_implicit_flush(FALSE);

      try {
        $data['result'] = call_user_func_array($function, $arguments);

      } catch (Exception $e) {
        ob_end_clean();
        throw $e;
      }

      $data['output'] = ob_get_clean();

      $this->_cache->set($key, $data, $namespace);
    }

    echo $data['output'];

    return $data['result'];
  }

  /**
   * @see PHI_Cache::clear()
   */
  public function clear()
  {
    $cacheDirectory = $this->_cache->getCacheBaseDirectory() . '/function';
    $files = scandir($cacheDirectory);

    foreach ($files as $file) {
      if ($file == '.' || $file == '..') {
        continue;
      }

      $path = $cacheDirectory . DIRECTORY_SEPARATOR . $file;
      PHI_FileUtils::deleteDirectory($path);
    }

    return TRUE;
  }
}
