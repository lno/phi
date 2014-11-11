<?php
/**
 * データベースによるキャッシュ管理機能を提供します。
 * この機能を有効にするには、あらかじめ "phi install-database-cache" コマンドを実行し、データベースにキャッシュテーブルを作成しておく必要があります。
 *
 * キャッシュテーブル作成スクリプト:
 * <code>
 * {PHI_ROOT_DIR}/skeleton/{database}/cache.sql
 * </code>
 *
 * @package cache
 */
class PHI_DatabaseCache extends PHI_Cache
{
  /**
   * @var PHI_DatabaseConnection
   */
  private $_connection;

  /**
   * コンストラクタ。
   */
  public function __construct()
  {
    $cacheConfig = PHI_Config::getApplication()->get('cache.database');

    if ($cacheConfig) {
      $dataSourceId = $cacheConfig->get('dataSource', PHI_DatabaseManager::DEFAULT_DATASOURCE_ID);
    } else {
      $dataSourceId = PHI_DatabaseManager::DEFAULT_DATASOURCE_ID;
    }

    $this->_connection = PHI_DatabaseManager::getInstance()->getConnection($dataSourceId);
  }

  /**
   * @param string $namespace キャッシュを格納する名前空間の指定。'foo.bar' のように '.' (ドット) で階層化することが出来ます。
   * @param int $expire キャッシュの有効期限秒。未指定時はキャッシュが削除されるまで値を持続します。
   * @see PHI_Cache::set()
   */
  public function set($name, $value, $namespace = NULL, $expire = PHI_Cache::EXPIRE_UNLIMITED)
  {
    if ($this->hasCached($name, $namespace)) {
      $this->delete($name, $namespace);
    }

    if ($namespace === NULL) {
      $namespace = $this->getNamespace();
    }

    if ($expire !== PHI_Cache::EXPIRE_UNLIMITED) {
      // 有効期限はデータベース時刻を使用する (ロードバランサ環境下で時刻のズレが発生する可能性があるため)
      $current = $this->_connection->getCommand()->expression('NOW()');

      $datetime = new DateTime($current);
      $datetime->add(new DateInterval(sprintf('PT%sS', $expire)));
      $expireDate = $datetime->format('Y-m-d H:i:s');

    } else {
      $expireDate = NULL;
    }

    $sql = 'INSERT INTO phi_caches(cache_name, cache_value, namespace, expire_date) '
          .'VALUES(:cache_name, :cache_value, :namespace, :expire_date)';

    $stmt = $this->_connection->createStatement($sql);
    $stmt->bindValue(':cache_name', $name);
    $stmt->bindValue(':cache_value', serialize($value));
    $stmt->bindValue(':namespace', $namespace);
    $stmt->bindValue(':expire_date', $expireDate);
    $stmt->execute();

    return TRUE;
  }

  /**
   * @param string $namespace キャッシュを格納する名前空間の指定。
   * @see PHI_Cache::get()
   */
  public function get($name, $namespace = NULL)
  {
    if ($namespace === NULL) {
      $namespace = $this->getNamespace();
    }

    $sql = 'SELECT cache_value, expire_date, NOW() AS current '
          .'FROM phi_caches '
          .'WHERE cache_name = :cache_name '
          .'AND namespace = :namespace';

    $stmt = $this->_connection->createStatement($sql);
    $stmt->bindValue(':cache_name', $name);
    $stmt->bindValue(':namespace', $namespace);
    $resultSet = $stmt->executeQuery();

    if ($record = $resultSet->read()) {
      if ($record->expire_date === NULL || PHI_DateUtils::unixtime($record->expire_date >= PHI_DateUtils::unixtime($record->current))) {
        return unserialize($record->cache_value);

      } else {
        $this->delete($name, $namespace);
      }
    }

    return NULL;
  }

  /**
   * @param string $namespace キャッシュを格納する名前空間の指定。
   * @see PHI_Cache::hasCached()
   */
  public function hasCached($name, $namespace = NULL)
  {
    if ($namespace === NULL) {
      $namespace = $this->getNamespace();
    }

    $sql = 'SELECT cache_name, expire_date, NOW() AS current '
          .'FROM phi_caches '
          .'WHERE cache_name = :cache_name '
          .'AND namespace = :namespace';

    $stmt = $this->_connection->createStatement($sql);
    $stmt->bindValue(':cache_name', $name);
    $stmt->bindValue(':namespace', $namespace);
    $resultSet = $stmt->executeQuery();

    if ($record = $resultSet->read()) {
      if (PHI_DateUtils::unixtime($record->expire_date) >= PHI_DateUtils::unixtime($record->current)) {
        return TRUE;

      } else {
        $this->delete($name, $namespace);
      }
    }

    return FALSE;
  }

  /**
   * @param string $namespace キャッシュを格納する名前空間の指定。
   * @see PHI_Cache::delete()
   */
  public function delete($name, $namespace = NULL)
  {
    if ($namespace === NULL) {
      $namespace = $this->getNamespace();
    }

    $sql = 'DELETE FROM phi_caches '
          .'WHERE cache_name = :cache_name '
          .'AND namespace = :namespace';

    $stmt = $this->_connection->createStatement($sql);
    $stmt->bindValue(':cache_name', $name);
    $stmt->bindValue(':namespace', $namespace);
    $affectedCount = $stmt->execute();

    if ($affectedCount) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * @see PHI_Cache::clear()
   */
  public function clear()
  {
    $this->_connection->rawQuery('TRUNCATE TABLE phi_caches');

    return TRUE;
  }

  /**
   * @param string $namespace キャッシュを格納する名前空間の指定。
   * @see PHI_Cache::getExpire()
   */
  public function getExpire($name, $namespace = NULL)
  {
    if ($namespace === NULL) {
      $namespace = $this->getNamespace();
    }

    $sql = 'SELECT expire_date '
          .'FROM phi_caches '
          .'WHERE cache_name = :cache_name '
          .'AND namespace = :namespace '
          .'AND expire_date >= NOW()';

    $stmt = $this->_connection->createStatement($sql);
    $stmt->bindValue(':cache_name', $name);
    $stmt->bindValue(':namespace', $namespace);
    $resultSet = $stmt->executeQuery();

    if ($expire = $resultSet->readField(0)) {
      return PHI_DateUtils::unixtime($expire);
    }

    return NULL;
  }
}
