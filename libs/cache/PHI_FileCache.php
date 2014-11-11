<?php
require PHI_LIBS_DIR . '/util/file/PHI_FileLock.php';

/**
 * ファイルによるキャッシュ管理機能を提供します。
 *
 * @package cache
 */
class PHI_FileCache extends PHI_Cache
{
  /**
   * キャッシュ基底ディレクトリ。
   * @var string
   */
  private $_basePath;

  /**
   * ロック制御フラグ。
   * @var bool
   */
  private $_lockMode = FALSE;

  /**
   * コンストラクタ。
   */
  public function __construct()
  {
    $this->_basePath = APP_ROOT_DIR . '/cache/file';
  }

  /**
   * ファイルロックモードを設定します。
   *
   * @param bool $lockMode ロックを有効にする場合は TRUE を指定。既定値は FALSE。
   */
  public function setLockMode($lockMode)
  {
    $this->_lockMode = $lockMode;
  }

  /**
   * キャッシュファイルは、APP_ROOT_DIR/cache/file/{$namespace} 以下に配置されます。
   *
   * @param string $namespace キャッシュを格納する名前空間の指定。'foo.bar' のように '.' (ドット) で階層化することが出来ます。
   * @param int $expire キャッシュの有効期限秒。未指定時はキャッシュが削除されるか無効 (キャッシュストレージの再起動など) になるまで値を持続します。
   * @param string $fileName キャッシュファイルの名前。未指定時は 32 文字から構成されるファイル名が付けられます。
   * @see PHI_Cache::set()
   */
  public function set($name, $value, $namespace = NULL, $expire = PHI_Cache::EXPIRE_UNLIMITED, $fileName = NULL)
  {
    $cachePath = $this->getCachePath($name, $namespace, $fileName);
    $data = serialize($value);
    $result = FALSE;

    if ($this->_lockMode) {
      $lock = new PHI_FileLock($cachePath);

      if ($lock->lock()) {
        $lock->lockFileWrite($data);
        $lock->unlock();
      }

    } else {
      file_put_contents($cachePath, $data);
    }

    @chmod($cachePath, 0777);

    if ($expire !== PHI_Cache::EXPIRE_UNLIMITED) {
      $expire += $_SERVER['REQUEST_TIME'];
    }

    if (@touch($cachePath, $expire) !== FALSE) {
      $result = TRUE;
    }

    return FALSE;
  }

  /**
   * @param string $namespace キャッシュを格納する名前空間の指定。
   * @param string $fileName 取得対象のキャッシュファイル名。
   * @see PHI_Cache::get()
   */
  public function get($name, $namespace = NULL, $fileName = NULL)
  {
    $cachePath = $this->getCachePath($name, $namespace, $fileName);
    $data = NULL;

    if (is_file($cachePath)) {
      $lastModify = filemtime($cachePath);

      if ($this->_lockMode) {
        $lock = new PHI_FileLock($cachePath);

        if ($lastModify == 0 || $_SERVER['REQUEST_TIME'] < $lastModify) {
          $data = unserialize($lock->lockFileOpen());

        } else {
          @unlink($cachePath);
        }

        $lock->unlock();

      } else {
        // 高負荷対策で @ 演算子を利用
        // @see /issues/59
        if ($lastModify == 0 || $_SERVER['REQUEST_TIME'] < $lastModify) {
          $content = @file_get_contents($cachePath);

          if ($content !== FALSE) {
            $data = unserialize($content);
          }

        } else {
          @unlink($cachePath);
        }
      }
    }

    return $data;
  }

  /**
   * @param string $namespace キャッシュを格納する名前空間の指定。
   * @param string $fileName キャッシュファイルの名前。
   * @see PHI_Cache::hasCached()
   */
  public function hasCached($name, $namespace = NULL, $fileName = NULL)
  {
    $cachePath = $this->getCachePath($name, $namespace, $fileName);

    if (is_file($cachePath)) {
      $lastModify = filemtime($cachePath);

      if ($lastModify == 0 || $_SERVER['REQUEST_TIME'] < $lastModify) {
        return TRUE;

      } else {
        @unlink($cachePath);
      }
    }

    return FALSE;
  }

  /**
   * @param string $namespace キャッシュを格納する名前空間の指定。
   * @param string $fileName キャッシュファイルの名前。
   * @see PHI_Cache::delete()
   */
  public function delete($name, $namespace = NULL, $fileName = NULL)
  {
    $cachePath = $this->getCachePath($name, $namespace, $fileName);

    if (is_file($cachePath)) {
      if (@unlink($cachePath) === FALSE) {
        return FALSE;
      }

      return TRUE;
    }

    return FALSE;
  }

  /**
   * @see PHI_Cache::clear()
   */
  public function clear()
  {
    $basePath = $this->_basePath;
    $files = scandir($basePath);

    foreach ($files as $file) {
      if ($file == '.' || $file == '..' || $file == 'autoload') {
        continue;
      }

      $path = $basePath . DIRECTORY_SEPARATOR . $file;
      PHI_FileUtils::deleteDirectory($path);
    }

    return TRUE;
  }

  /**
   * @param string $namespace キャッシュを格納する名前空間の指定。
   * @param string $fileName キャッシュファイルの名前。
   * @see PHI_Cache::getExpire()
   */
  public function getExpire($name, $namespace = NULL, $fileName = NULL)
  {
    $cachePath = $this->getCachePath($name, $namespace, $fileName);

    if (is_file($cachePath)) {
      $lastModify = filemtime($cachePath);

      if ($lastModify == 0 || $_SERVER['REQUEST_TIME'] < $lastModify) {
        return $lastModify;

      } else {
        unlink($cachePath);
      }
    }

    return NULL;
  }

  /**
   * @param string $namespace キャッシュを格納する名前空間の指定。
   * @param string $fileName キャッシュファイルの名前。
   * @see PHI_Cache::getCachePath()
   */
  public function getCachePath($name, $namespace = NULL, $fileName = NULL)
  {
    if ($namespace === NULL) {
      $namespace = $this->getNamespace();
    }

    if ($fileName === NULL) {
      if ($this->getCachePathType() == PHI_Cache::CACHE_PATH_TYPE_PLANE) {
        $fileName = $name;
      } else {
        $fileName = md5($name);
      }
    }

    $cacheDirectory = $this->_basePath . DIRECTORY_SEPARATOR . str_replace($this->getNamespaceDelimiter(), DIRECTORY_SEPARATOR, $namespace);

    if (!is_dir($cacheDirectory)) {
      PHI_FileUtils::createDirectory($cacheDirectory);
    }

    $cachePath = $cacheDirectory . DIRECTORY_SEPARATOR . $fileName;

    return $cachePath;
  }

  /**
   * ファイルキャッシュの基底ディレクトリパスを取得します。
   *
   * @return string ファイルキャッシュの基底ディレクトリパスを返します。
   */
  public function getCacheBaseDirectory()
  {
    return $this->_basePath;
  }
}
