<?php
require PHI_LIBS_DIR . '/kernel/container/PHI_Object.php';
require PHI_LIBS_DIR . '/kernel/observer/PHI_KernelEventObserver.php';

require PHI_LIBS_DIR . '/kernel/loader/PHI_ClassLoader.php';
require PHI_LIBS_DIR . '/kernel/path/PHI_AppPathManager.php';
require PHI_LIBS_DIR . '/kernel/handler/PHI_ErrorHandler.php';
require PHI_LIBS_DIR . '/kernel/handler/PHI_ExceptionHandler.php';
require PHI_LIBS_DIR . '/kernel/observer/listener/PHI_ApplicationEventListener.php';

/**
 * フレームワークを起動するブートローダ機能を提供します。
 *
 * @package kernel.loader
 */
class PHI_BootLoader
{
  /**
   * 起動モード定数。(Web アプリケーション)
   */
  const BOOT_MODE_WEB = 1;

  /**
   * 起動モード定数。(コンソールアプリケーション)
   */
  const BOOT_MODE_CONSOLE = 2;

  /**
   * 起動モード定数。(phi コマンド)
   */
  const BOOT_MODE_COMMAND = 4;

  /**
   * コンフィグ定数。(デフォルト)
   */
  const CONFIG_TYPE_DEFAULT = 1;

  /**
   * コンフィグ定数。(ポリシーコンフィグの参照)
   */
  const CONFIG_TYPE_POLICY = 2;

  /**
   * @var int
   */
  private static $_bootMode;

  /**
   * @var int
   */
  private static $_configType;

  /**
   * Web アプリケーションを開始します。
   */
  public static function startWebApplication()
  {
    require PHI_LIBS_DIR . '/controller/PHI_WebApplication.php';
    require PHI_LIBS_DIR . '/kernel/observer/listener/PHI_WebApplicationEventListener.php';

    self::$_bootMode = self::BOOT_MODE_WEB;
    self::$_configType = self::CONFIG_TYPE_DEFAULT;
    self::startApplication();

    PHI_FrontController::getInstance()->dispatch();
  }

  /**
   * コントロールパネルを開始します。
   */
  public static function startControlPanel()
  {
    self::$_bootMode = self::BOOT_MODE_WEB;
    self::$_configType = self::CONFIG_TYPE_POLICY;

    self::startApplication();

    // cpanel モジュールをクラスローダに追加
    PHI_ClassLoader::addSearchPath(PHI_ROOT_DIR . '/webapps/cpanel/libs');

    // 設定ファイルの初期化
    $appConfig = PHI_Config::getApplication();
    $projectAppConfig = PHI_Config::get(PHI_Config::TYPE_DEFAULT_APPLICATION);

    $appConfig->set('database', $projectAppConfig->getArray('database'));
    $appConfig->set('cpanel', $projectAppConfig->getArray('cpanel'));
    $appConfig->set('response.callback', 'none');

    PHI_FrontController::getInstance()->dispatch();
  }

  /**
   * コンソールアプリケーションを開始します。
   */
  public static function startConsoleApplication()
  {
    require PHI_LIBS_DIR . '/kernel/observer/listener/PHI_ConsoleApplicationEventListener.php';

    self::$_bootMode = self::BOOT_MODE_CONSOLE;
    self::$_configType = self::CONFIG_TYPE_DEFAULT;

    self::startApplication();

    PHI_Console::getInstance()->start();
  }

  /**
   * phi コマンドを開始します。
   */
  public static function startConsoleCommand()
  {
    self::$_bootMode = self::BOOT_MODE_COMMAND;
    self::$_configType = self::CONFIG_TYPE_POLICY;

    set_error_handler(array('PHI_ErrorHandler', 'handler'));
    set_exception_handler(array('PHI_ExceptionHandler', 'handler'));

    PHI_ClassLoader::initialize();
    $manager = PHI_AppPathManager::getInstance();

    if (defined('APP_ROOT_DIR')) {
      $themeConfig = PHI_Config::getApplication()->get('theme');
      $manager->initialize($themeConfig);

    } else {
      $manager->initialize();
    }
  }

  /**
   * ブートローダの起動モードを取得します。
   *
   * @return string ブートローダの起動モードを返します。
   */
  public static function getBootMode()
  {
    return self::$_bootMode;
  }

  /**
   * ブートローダが Web アプリケーションモードで起動しているかどうかチェックします。
   *
   * @return bool ブートローダが Web アプリケーションモードで起動している場合に TRUE を返します。
   */
  public static function isBootTypeWeb()
  {
    if (self::getBootMode() == self::BOOT_MODE_WEB) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * ブートローダがコンソールモードで起動しているかどうかチェックします。
   *
   * @return bool ブートローダがコンソールモードで起動している場合に TRUE を返します。
   */
  public static function isBootTypeConsole()
  {
    if (self::getBootMode() == self::BOOT_MODE_CONSOLE) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * ブートローダがコマンドモードで起動しているかどうかチェックします。
   *
   * @return bool ブートローダがコマンドモードで起動している場合に TRUE を返します。
   */
  public static function isBootTypeCommand()
  {
    if (self::getBootMode() == self::BOOT_MODE_COMMAND) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * コンフィグの参照がアプリケーションモードであるかどうかチェックします。
   *
   * @return bool コンフィグの参照がアプリケーションモードの場合に TRUE を返します。
   */
  public static function isConfigTypeDefault()
  {
    if (self::$_configType == self::CONFIG_TYPE_DEFAULT) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * コンフィグの参照がポリシーモードであるかどうかチェックします。
   *
   * @return bool コンフィグの参照がポリシーモードの場合に TRUE を返します。
   */
  public static function isConfigTypePolicy()
  {
    if (self::$_configType == self::CONFIG_TYPE_POLICY) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * アプリケーションを開始します。
   *
   * @throws PHI_ConfigurationException {@link ini_set()} に失敗した場合に発生。
   */
  private static function startApplication()
  {
    // エラー、例外ハンドラの登録
    set_error_handler(array('PHI_ErrorHandler', 'handler'));
    set_exception_handler(array('PHI_ExceptionHandler', 'handler'));
    register_shutdown_function(array('PHI_ErrorHandler', 'detectFatalError'));

    // クラスローダの初期化
    PHI_ClassLoader::initialize();

    $config = PHI_Config::getApplication();

    // php.ini のオーバーライド
    $phpConfig = $config->getArray('php');

    foreach ($phpConfig as $name => $value) {
      if (ini_set($name, $value) === FALSE) {
        $message = sprintf('Can\'t set \'%s\'. Can only set PHP_INI_USER or PHP_INI_ALL.', $name);
        throw new PHI_ConfigurationException($message);
      }
    }

    // アプリケーションパスの設定
    $themeConfig = $config->get('theme');
    PHI_AppPathManager::getInstance()->initialize($themeConfig);

    // オートロードパスの追加
    $autoloadConfig = $config->getArray('autoload');

    foreach ($autoloadConfig as $path) {
      PHI_ClassLoader::addSearchPath($path);
    }

    PHI_DIContainerFactory::Initialize();
  }
}

