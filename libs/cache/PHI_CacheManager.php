<?php
/**
 * ファイルやメモリを使ったキャッシュ管理機能を提供します。
 *
 * @package cache
 */
class PHI_CacheManager extends PHI_Object
{
  /**
   * NULL キャッシュ定数。(キャッシュしない)
   */
  const CACHE_TYPE_NULL = 'null';

  /**
   * ファイルキャッシュ定数。
   */
  const CACHE_TYPE_FILE = 'file';

  /**
   * APC キャッシュ定数。
   */
  const CACHE_TYPE_APC = 'apc';

  /**
   * XCache キャッシュ定数。
   */
  const CACHE_TYPE_XCACHE = 'xcache';

  /**
   * EAccelerator キャッシュ定数。
   */
  const CACHE_TYPE_EACCELERATOR = 'eaccelerator';

  /**
   * memcache キャッシュ定数。
   */
  const CACHE_TYPE_MEMCACHE = 'memcach';

  /**
   * データベースキャッシュ定数。
   */
  const CACHE_TYPE_DATABASE = 'database';

  /**
   * コンストラクタ。
   */
  private function __construct()
  {}

  /**
   * 指定したキャッシュタイプのインスタンスを取得します。
   *
   * @param string $type キャッシュタイプ定数 CACHE_TYPE_* の指定。
   * @param array $options キャッシュストレージオプション。
   * @return PHI_Cache PHI_Cache を実装したキャッシュオブジェクトのインスタンスを返します。
   * @throws InvalidArgumentException type に渡された値が不正な場合に発生。
   */
  public static function getInstance($type, array $options = array())
  {
    static $instance = array();

    if (empty($instance[$type])) {
      switch ($type) {
        case self::CACHE_TYPE_FILE:
          $className = 'PHI_FileCache';
          break;

        case self::CACHE_TYPE_APC:
          $className = 'PHI_APCCache';
          break;

        case self::CACHE_TYPE_XCACHE:
          $className = 'PHI_XCacheCache';
          break;

        case self::CACHE_TYPE_EACCELERATOR:
          $className = 'PHI_EAcceleratorCache';
          break;

        case self::CACHE_TYPE_MEMCACHE:
          $className = 'PHI_MemcacheCache';
          break;

        case self::CACHE_TYPE_DATABASE:
          $className = 'PHI_DatabaseCache';
          break;

        case self::CACHE_TYPE_NULL:
          $className = 'PHI_NullCache';
          break;

        default:
          throw new InvalidArgumentException('Cache type is illegal.');
      }

      $classPath = PHI_LIBS_DIR . '/cache/' . $className . '.php';

      PHI_ClassLoader::loadByPath($classPath, $className);
      $instance[$type] = new $className($options);
    }

    return $instance[$type];
  }
}
