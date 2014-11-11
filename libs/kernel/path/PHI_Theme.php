<?php
/**
 * テーマを管理します。
 *
 * @package kernel.path
 */
class PHI_Theme
{
  /**
   * デフォルトのテーマ名。
   */
  const DEFAULT_THEME_NAME = 'none';

  /**
   * @var PHI_ParameterHolder
   */
  private $_themeConfig;

  /**
   * @var string
   */
  private $_themePath;

  /**
   * @var string
   */
  private $_themeName;

  /**
   * @var bool
   */
  private $_isActive = FALSE;

  /**
   * @var array
   */
  private $_moduleNames = array();

  /**
   * @var array
   */
  private $_ignoreModuleNames = array('cpanel');

  /**
   * @var array
   */
  private $_extensionPaths = array();

  /**
   * コンストラクタ。
   *
   * @param PHI_ParameterHolder $themeConfig テーマ属性。
   */
  public function __construct(PHI_ParameterHolder $themeConfig)
  {
    $this->_themeConfig = $themeConfig;

    $themeName = NULL;
    $hasDomainTheme = FALSE;

    // ドメイン指定がある場合
    $domainConfig = $themeConfig->get('domain');

    if (PHI_BootLoader::isBootTypeWeb() && $domainConfig) {
      $serverName = $_SERVER['SERVER_NAME'];

      foreach ($domainConfig as $domain => $domainTheme) {
        // サブドメイン指定時
        if (($pos = strrpos($domain, '*.')) !== FALSE) {
          $domain = substr($domain, $pos + 2);

          // サブドメインを含まないホスト名、あるいはサブドメインを含むホスト名にマッチ
          if ($domain === $serverName || strpos($serverName, '.' . $domain) !== FALSE) {
            $hasDomainTheme = TRUE;
            $themeName = $domainTheme;

            break;
          }

        // ドメインにマッチするテーマが指定されている
        } else if ($domain === $serverName) {
          $hasDomainTheme = TRUE;
          $themeName = $domainTheme;

          break;
        }
      }
    }

    // ドメインに合致するテーマが見つからない場合
    if (!$hasDomainTheme) {
      $themeName = $themeConfig->getString('name');
    }

    if (PHI_StringUtils::nullOrEmpty($themeName)) {
      $this->_themeName = self::DEFAULT_THEME_NAME;

    } else {
      $this->_moduleNames = $themeConfig->getArray('modules');
      $this->_themeName = $themeName;
      $this->_extensionPaths = $themeConfig->getArray('extension');

      if ($themeName !== self::DEFAULT_THEME_NAME) {
        $this->_isActive = TRUE;
      }
    }

    $this->setThemePath($themeName);
  }

  /**
   * テーマディレクトリを設定します。
   *
   * @param string $themeName テーマ名。
   */
  private function setThemePath($themeName)
  {
    if ($themeName === self::DEFAULT_THEME_NAME) {
      $this->_themePath = APP_ROOT_DIR;

    } else {
      $themePath = sprintf('%s%s%s',
        $this->_themeConfig['basePath'],
        DIRECTORY_SEPARATOR,
        $themeName);

      if (PHI_FileUtils::isAbsolutePath($themePath)) {
        $validPath = $themePath;
      } else {
        $validPath = APP_ROOT_DIR . DIRECTORY_SEPARATOR . $themePath;
      }

      if (is_dir($validPath)) {
        $this->_themePath = $validPath;

      } else {
        $message = sprintf('Can not find theme directory. [%s]', $themePath);
        throw new PHI_IOException($message);
      }
    }
  }

  /**
   * テーマディレクトリの基底パスを取得します。
   *
   * @return string テーマディレクトリの基底パスを返します。
   * @param string $moduleName 対象モジュール名。
   */
  public function getThemePath()
  {
    return $this->_themePath;
  }

  /**
   * 指定したパスがテーマ拡張ディレクトリとして有効な状態にあるかどうかチェックします。
   *
   * @return bool 拡張ディレクトリとして有効な場合は TRUE、無効であれば FALSE を返します。
   */
  public function isActiveExtensionPath($path)
  {
    if ($this->_isActive && in_array($path, $this->_extensionPaths)) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * モジュールをテーマに追加します。
   *
   * @param string $moduleName 対象モジュール名。
   */
  public function addModuleName($moduleName)
  {
    if (!in_array($moduleName, $this->_ignoreModuleNames) && !in_array($moduleName, $this->_moduleNames)) {
      $this->_moduleNames[] = $moduleName;
    }
  }

  /**
   * 対象モジュールがテーマとして有効な状態にあるかどうかチェックします。
   *
   * @return bool テーマが有効な場合に TRUE、無効な場合は FALSE を返します。
   * @param string $module 対象モジュール名。
   */
  public function hasModuleName($moduleName)
  {
    $result = FALSE;

    if (!in_array($moduleName, $this->_ignoreModuleNames) &&
      $this->_isActive &&
      in_array($moduleName, $this->_moduleNames)) {

      $result = TRUE;
    }

    return $result;
  }

  /**
   * テーマからモジュールを削除します。
   *
   * @param string $moduleName モジュール名。
   * @return bool 削除が成功した場合は TRUE、失敗した場合は FALSE を返します。
   */
  public function removeModuleName($moduleName)
  {
    $result = FALSE;
    $key = array_search($moduleName, $this->_moduleNames);

    if ($key !== FALSE) {
      unset($this->_moduleNames[$key]);
      $result = TRUE;
    }

    return $result;
  }

  /**
   * テーマ名を設定します。
   *
   * @param string $themeName テーマ名。
   */
  public function setThemeName($themeName)
  {
    $this->setThemePath($themeName);
    $this->_themeName = $themeName;

    if ($themeName === self::DEFAULT_THEME_NAME) {
      $this->_isActive = FALSE;
    } else {
      $this->_isActive = TRUE;
    }
  }

  /**
   * 現在有効なテーマ名を取得します。
   *
   * @return string 現在有効なテーマ名を返します。
   */
  public function getThemeName()
  {
    return $this->_themeName;
  }

  /**
   * テーマ機能が有効な状態かどうかチェックします。
   * 特定のモジュールでテーマが有効か確認するには、{@link hasModuleName()} メソッドを使用して下さい。
   *
   * @return bool テーマ機能が有効な場合は TRUE、無効な場合は FALSE を返します。
   */
  public function isActive()
  {
    return $this->_isActive;
  }
}
