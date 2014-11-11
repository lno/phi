<?php
/**
 * 各種設定ファイル (YAML) の参照、及び編集機能を提供します。
 * 参照された YAML は Spyc によりパースされ、{@link PHI_YAMLCache} を介して内部キャッシュされます。
 * <i>キャッシュファイルは {APP_ROOT_DIR}/config/yaml ディレクトリ下に作成されます。
 * 設定ファイルが書き換えられた (最終更新時間が更新された) 場合は自動的にキャッシュを再構築するため、基本的に手動でキャッシュを削除する必要はありません。 </i>
 *
 * @package util.config
 */
class PHI_Config extends PHI_Object
{
  /**
   * YAML 定数。(config/application.yml、config/application_{hostname}.yml)
   */
  const TYPE_DEFAULT_APPLICATION = 1;

  /**
   * YAML 定数。(config/base_dicon.yml)
   */
  const TYPE_DEFAULT_BASE_DICON = 2;

  /**
   * YAML 定数。(config/routes.yml)
   */
  const TYPE_DEFAULT_ROUTES = 3;

  /**
   * YAML 定数。(config/global_filters.yml)
   */
  const TYPE_DEFAULT_GLOBAL_FILTERS = 4;

  /**
   * YAML 定数。(modules/{module}/config/filters.yml)
   */
  const TYPE_DEFAULT_MODULE_FILTERS = 5;

  /**
   * YAML 定数。(config/global_behavior.yml)
   */
  const TYPE_DEFAULT_GLOBAL_BEHAVIOR = 6;

  /**
   * YAML 定数。(modules/{module}/config/behavior.yml)
   */
  const TYPE_DEFAULT_MODULE_BEHAVIOR = 7;

  /**
   * YAML 定数。(modules/{module}/behaviors/{action}.yml)
   */
  const TYPE_DEFAULT_ACTION_BEHAVIOR = 8;

  /**
   * YAML 定数。(config/global_helpers.yml)
   */
  const TYPE_DEFAULT_GLOBAL_HELPERS = 9;

  /**
   * YAML 定数。(modules/{module}/config/helpers.yml)
   */
  const TYPE_DEFAULT_MODULE_HELPERS = 10;

  /**
   * YAML 定数。(config/site.yml、config/site_{hostname}.yml)
   */
  const TYPE_DEFAULT_SITE = 11;

  /**
   * YAML 定数。(カスタムパス)
   */
  const TYPE_DEFAULT_CUSTOM = 12;

  /**
   * YAML 定数。({PHI_ROOT_DIR}/skeleton/policy_config/application.yml)
   */
  const TYPE_POLICY_APPLICATION = 13;

  /**
   * YAML 定数。({PHI_ROOT_DIR}/skeleton/policy_config/base_dicon.yml)
   */
  const TYPE_POLICY_BASE_DICON = 14;

  /**
   * YAML 定数。({PHI_ROOT_DIR}/skeleton/policy_config/routes.yml)
   */
  const TYPE_POLICY_ROUTES = 15;

  /**
   * YAML 定数。({PHI_ROOT_DIR}/skeleton/policy_config/filters.yml)
   */
  const TYPE_POLICY_GLOBAL_FILTERS = 16;

  /**
   * YAML 定数。({PHI_ROOT_DIR}/skeleton/policy_config/helpers.yml)
   */
  const TYPE_POLICY_GLOBAL_HELPERS = 17;

  /**
   * @var array
   */
  protected static $_gets = array();

  /**
   * @param int $globalConfigType
   * @param int $moduleConfigType
   * @return array
   */
  private static function merge($globalConfigType, $moduleConfigType)
  {
    if (PHI_BootLoader::isBootTypeWeb()) {
      if (PHI_BootLoader::isConfigTypeDefault()) {
        $config1 = self::getArray($globalConfigType);
      } else {
        $config1 = self::getPolicy($globalConfigType);
      }

      $config2 = self::getArray($moduleConfigType);

      if ($config2) {
        $merge = PHI_ArrayUtils::merge($config1, $config2);
      } else {
        $merge = $config1;
      }

    } else {
      if (PHI_BootLoader::isConfigTypeDefault()) {
        $merge = self::getArray($globalConfigType);
      } else {
        $merge = self::getPolicy($globalConfigType);
      }
    }

    return $merge;
  }

  /**
   * アプリケーション設定ファイルを読み込みます。
   * ファイルは次の順でマージされます。(一番下が最優先)
   *   o config/application.yml
   *   o config/application_{hostname}.yml
   *
   * @return PHI_ParameterHolder ファイルに含まれる設定情報を返します。
   */
  public static function getApplication()
  {
    if (empty(self::$_gets['application'])) {
      if (PHI_BootLoader::isConfigTypeDefault()) {
        $config = self::getArray(self::TYPE_DEFAULT_APPLICATION);
      } else {
        $config = self::getPolicy(self::TYPE_POLICY_APPLICATION);
      }

      self::$_gets['application'] = new PHI_ParameterHolder($config, TRUE);
    }

    return self::$_gets['application'];
  }

  /**
   * DI コンテナ設定ファイルを読み込みます。
   *
   * @return PHI_ParameterHolder ファイルに含まれる設定情報を返します。
   */
  public static function getDIContainer()
  {
    if (empty(self::$_gets['base_dicon'])) {
      if (PHI_BootLoader::isConfigTypeDefault()) {
        $config = self::getArray(self::TYPE_DEFAULT_BASE_DICON);
      } else {
        $config = self::getPolicy(self::TYPE_POLICY_BASE_DICON);
      }

      self::$_gets['base_dicon'] = new PHI_ParameterHolder($config, TRUE);
    }

    return self::$_gets['base_dicon'];
  }

  /**
   * ルータ設定ファイルを読み込みます。
   *
   * @return PHI_ParameterHolder ファイルに含まれる設定情報を返します。
   */
  public static function getRoutes()
  {
    if (empty(self::$_gets['routes'])) {
      if (PHI_BootLoader::isConfigTypeDefault()) {
        $config = self::getArray(self::TYPE_DEFAULT_ROUTES);
      } else {
        $config = self::getPolicy(self::TYPE_POLICY_ROUTES);
      }

      self::$_gets['routes'] = new PHI_ParameterHolder($config, TRUE);
    }

    return self::$_gets['routes'];
  }

  /**
   * フィルタ設定ファイルを読み込みます。
   * ファイルは次の順でマージされます。(一番下が最優先)
   *   o config/global_filters.yml
   *   o modules/{module_name}/config/filters.yml
   *
   * @return PHI_ParameterHolder ファイルに含まれる設定情報を返します。
   */
  public static function getFilters()
  {
    if (empty(self::$_gets['filters'])) {
      if (PHI_BootLoader::isConfigTypeDefault()) {
        $config = self::merge(self::TYPE_DEFAULT_GLOBAL_FILTERS, self::TYPE_DEFAULT_MODULE_FILTERS);
      } else {
        $config = self::merge(self::TYPE_POLICY_GLOBAL_FILTERS, self::TYPE_DEFAULT_MODULE_FILTERS);
      }

      self::$_gets['filters'] = new PHI_ParameterHolder($config, TRUE);
    }

    return self::$_gets['filters'];
  }

  /**
   * ビヘイビア設定ファイルを読み込みます。
   * ファイルは次の順でマージされます。(一番下が最優先)
   *   o config/global_behavior.yml
   *   o modules/{module_name}/config/behavior.yml
   *   o modules/{module_name}/behaviors/{action_name}.yml
   *
   * @param bool $throw TRUE 指定時はアクションビヘイビアが見つからない場合に例外を発生させます。
   * @return PHI_ParameterHolder ファイルに含まれる設定情報を返します。
   * @throws PHI_IOException アクションビヘイビアが見つからない場合に発生。(throw が TRUE の場合のみ)
   */
  public static function getBehavior($actionName = NULL, $throw = FALSE)
  {
    if ($actionName === NULL) {
      $route = PHI_FrontController::getInstance()->getRequest()->getRoute();
      $actionName = $route->getForwardStack()->getLast()->getAction()->getActionName();
    }

    if (empty(self::$_gets['behavior'])) {
      $merge = self::merge(self::TYPE_DEFAULT_GLOBAL_BEHAVIOR, self::TYPE_DEFAULT_MODULE_BEHAVIOR);
      self::$_gets['behavior'] = new PHI_ParameterHolder($merge, TRUE);
    }

    // アクションビヘイビアをマージ
    if (PHI_BootLoader::isBootTypeWeb()) {
      static $actionConfigs = array();

      if (isset($actionConfigs[$actionName])) {
        $actionConfig = $actionConfigs[$actionName];

      } else {
        $actionConfig = self::getArray(self::TYPE_DEFAULT_ACTION_BEHAVIOR, $actionName);
        $actionConfigs[$actionName] = $actionConfig;
      }

      if ($throw && $actionConfig === FALSE) {
        $message = sprintf('Can not find the specified behavior. [%s]', $actionName);
        throw new PHI_IOException($message);
      }

      $holder = clone self::$_gets['behavior'];
      $holder->merge($actionConfig);
    }

    return $holder;
  }

  /**
   * ヘルパ設定ファイルを読み込みます。
   * ファイルは次の順でマージされます。(一番下が最優先)
   *   o config/global_helpers.yml
   *   o modules/{module_name}/config/helpers.yml
   *
   * @return PHI_ParameterHolder ファイルに含まれる設定情報を返します。
   */
  public static function getHelpers()
  {
    if (empty(self::$_gets['helpers'])) {
      if (PHI_BootLoader::isConfigTypeDefault()) {
        $config = self::merge(self::TYPE_DEFAULT_GLOBAL_HELPERS, self::TYPE_DEFAULT_MODULE_HELPERS);
      } else {
        $config = self::merge(self::TYPE_POLICY_GLOBAL_HELPERS, self::TYPE_DEFAULT_MODULE_HELPERS);
      }

      self::$_gets['helpers'] = new PHI_ParameterHolder($config, TRUE);
    }

    return self::$_gets['helpers'];
  }

  /**
   * サイト設定ファイルを読み込みます。
   * ファイルは次の順でマージされます。(一番下が最優先)
   *   o config/site.yml
   *   o config/site_{hostname}.yml
   *
   * @return PHI_ParameterHolder ファイルに含まれる設定情報を返します。
   */
  public static function getSite()
  {
    if (empty(self::$_gets['site'])) {
      $config = self::getArray(self::TYPE_DEFAULT_SITE);
      self::$_gets['site'] = new PHI_ParameterHolder($config, TRUE);
    }

    return self::$_gets['site'];
  }

  /**
   * カスタム設定ファイルを作成します。
   * ファイルは次の順でマージされます。(一番下が最優先)
   *   o config/{custom_name}.yml
   *   o config/{custom_name}_{hostname}.yml
   *
   * @param string $path APP_ROOT_DIR/config、または '@{path}' 形式の APP_ROOT_DIR から始まるパス。
   *   拡張子を付ける必要はありません。
   * @return PHI_ConfigCustomHolder PHI_ConfigCustomHolder のインスタンスを返します。
   */
  public static function createCustomFile($path)
  {
    $path = PHI_AppPathManager::buildAbsolutePath('config', $path, 'yml');

    $holder = new PHI_ConfigCustomHolder($path);
    $holder->update();

    return $holder;
  }

  /**
   * カスタム設定ファイルを読み込みます。
   *
   * @param string $path APP_ROOT_DIR/config、または '@{path}' 形式の APP_ROOT_DIR から始まるパス。
   *   拡張子を付ける必要はありません。
   * @return PHI_ParameterHolder ファイルに含まれる設定情報を返します。
   */
  public static function getCustomFile($path)
  {
    $path = PHI_AppPathManager::buildAbsolutePath('config', $path, 'yml');

    $config = self::getArray(self::TYPE_DEFAULT_CUSTOM, $path);
    $holder = new PHI_ConfigCustomHolder($path, $config);

    return $holder;
  }

  /**
   * アプリケーションの設定ファイルを取得します。
   *
   * @param int $type {@link getPath()} メソッドを参照。
   * @param string $include {@link getPath()} メソッドを参照。
   * @return PHI_ParameterHolder 設定ファイルに定義されたデータを PHI_ParameterHolder オブジェクト形式で返します。
   */
  public static function get($type, $include = NULL)
  {
    return new PHI_ParameterHolder(self::getArray($type, $include), TRUE);
  }

  /**
   * 設定ファイルのパスを取得します。
   * このメソッドは実際に対象ファイルが存在するかどうかのチェックは行いません。
   *
   * @param int $type 参照するファイルタイプ。PHI_Config::TYPE_DEFAULT_* 定数を指定。
   * @param string $include 参照するファイルを指定します。
   *   type が {@link TYPE_DEFAULT_ACTION_BEHAVIOR}、あるいは {@link TYPE_DEFAULT_CUSTOM} の場合に有効です。
   *   - TYPE_DEFAULT_CUSTOM: 対象ファイルを絶対パス、または {APP_ROOT_DIR} からの相対パスで指定。
   *   - TYPE_DEFAULT_ACTION_BEHAVIOR: 参照するアクション名を指定。
   * @return string ファイルパスを返します。パスが取得できない場合は FALSE を返します。
   */
  public static function getPath($type, $include = NULL)
  {
    $path = FALSE;

    switch ($type) {
      case self::TYPE_DEFAULT_APPLICATION:
        $path = sprintf('%s%sconfig%sapplication.yml',
          APP_ROOT_DIR,
          DIRECTORY_SEPARATOR,
          DIRECTORY_SEPARATOR);
        break;

      case self::TYPE_DEFAULT_BASE_DICON:
        $path = sprintf('%s%sconfig%sbase_dicon.yml',
          APP_ROOT_DIR,
          DIRECTORY_SEPARATOR,
          DIRECTORY_SEPARATOR);
        break;

      case self::TYPE_DEFAULT_ROUTES:
        $path = sprintf('%s%sconfig%sroutes.yml',
          APP_ROOT_DIR,
          DIRECTORY_SEPARATOR,
          DIRECTORY_SEPARATOR);
        break;

      case self::TYPE_DEFAULT_GLOBAL_FILTERS:
        $path = sprintf('%s%sconfig%sglobal_filters.yml',
          APP_ROOT_DIR,
          DIRECTORY_SEPARATOR,
          DIRECTORY_SEPARATOR);
        break;

      case self::TYPE_DEFAULT_MODULE_FILTERS:
        if (PHI_BootLoader::isBootTypeWeb()) {
          $route = PHI_FrontController::getInstance()->getRequest()->getRoute();

          if ($route) {
            $modulePath = PHI_AppPathManager::getInstance()->getModulePath($route->getModuleName());

            $path = sprintf('%s%sconfig%sfilters.yml',
              $modulePath,
              DIRECTORY_SEPARATOR,
              DIRECTORY_SEPARATOR);
          }
        }

        break;

      case self::TYPE_DEFAULT_GLOBAL_BEHAVIOR:
        $path = sprintf('%s%sconfig%sglobal_behavior.yml',
          APP_ROOT_DIR,
          DIRECTORY_SEPARATOR,
          DIRECTORY_SEPARATOR);

        break;

      case self::TYPE_DEFAULT_MODULE_BEHAVIOR:
        if (PHI_BootLoader::isBootTypeWeb()) {
          $route = PHI_FrontController::getInstance()->getRequest()->getRoute();

          if ($route) {
            $modulePath = PHI_AppPathManager::getInstance()->getModulePath($route->getModuleName());

            $path = sprintf('%s%sconfig%sbehavior.yml',
              $modulePath,
              DIRECTORY_SEPARATOR,
              DIRECTORY_SEPARATOR);
          }
        }

        break;

      case self::TYPE_DEFAULT_ACTION_BEHAVIOR:
        if (PHI_BootLoader::isBootTypeWeb()) {
          $route = PHI_FrontController::getInstance()->getRequest()->getRoute();
          $basePath = dirname($route->getForwardStack()->getLast()->getAction()->getBehaviorPath());
          $path = PHI_AppPathManager::buildAbsolutePath($basePath, $include, '.yml');
        }

        break;

      case self::TYPE_DEFAULT_GLOBAL_HELPERS:
        $path = sprintf('%s%sconfig%sglobal_helpers.yml',
          APP_ROOT_DIR,
          DIRECTORY_SEPARATOR,
          DIRECTORY_SEPARATOR);

        break;

      case self::TYPE_DEFAULT_MODULE_HELPERS:
        if (PHI_BootLoader::isBootTypeWeb()) {
          $route = PHI_FrontController::getInstance()->getRequest()->getRoute();

          if ($route) {
            $modulePath = PHI_AppPathManager::getInstance()->getModulePath($route->getModuleName());

            $path = sprintf('%s%sconfig%shelpers.yml',
              $modulePath,
              DIRECTORY_SEPARATOR,
              DIRECTORY_SEPARATOR);
          }
        }

        break;

      case self::TYPE_DEFAULT_SITE:
        $path = sprintf('%s%sconfig%ssite.yml',
          APP_ROOT_DIR,
          DIRECTORY_SEPARATOR,
          DIRECTORY_SEPARATOR);
        break;

      case self::TYPE_DEFAULT_CUSTOM:
        $path = PHI_FileUtils::buildAbsolutePath($include);
        break;

      case self::TYPE_POLICY_APPLICATION:
        $path = PHI_ROOT_DIR . '/skeleton/blank_application/config/application.yml';
        break;

      case self::TYPE_POLICY_BASE_DICON:
        $path = PHI_ROOT_DIR . '/skeleton/blank_application/config/base_dicon.yml';
        break;

      case self::TYPE_POLICY_ROUTES:
        $path = PHI_ROOT_DIR . '/skeleton/blank_application/config/routes.yml';
        break;

      case self::TYPE_POLICY_GLOBAL_FILTERS:
        $path = PHI_ROOT_DIR . '/skeleton/blank_application/config/global_filters.yml';
        break;

      case self::TYPE_POLICY_GLOBAL_HELPERS:
        $path = PHI_ROOT_DIR . '/skeleton/blank_application/config/global_helpers.yml';
        break;
    }

    return $path;
  }

  /**
   * アプリケーションの設定ファイルを取得します。
   *
   * @param int $type {@link getPath()} メソッドを参照。
   * @param string $include {@link getPath()} メソッドを参照。
   * @return array 設定ファイルに定義されたデータを配列形式で返します。
   */
  public static function getArray($type, $include = NULL)
  {
    static $cache;

    if ($cache === NULL) {
      $cache = new PHI_YAMLCache();
    }

    $path = self::getPath($type, $include);
    $config = array();

    switch ($type) {
      case self::TYPE_DEFAULT_APPLICATION:
        $config = $cache->get($path, array('compileApplication'), TRUE);
        break;

      case self::TYPE_DEFAULT_BASE_DICON:
        $config = $cache->get($path, array('compileBaseDicon'));
        break;

      case self::TYPE_DEFAULT_ROUTES:
        $config = $cache->get($path, array('compileRoutes'));
        break;

      case self::TYPE_DEFAULT_GLOBAL_FILTERS:
      case self::TYPE_DEFAULT_MODULE_FILTERS:
        if ($type == self::TYPE_DEFAULT_GLOBAL_FILTERS) {
          $callback = 'compileGlobalFilters';
        } else {
          $callback = 'compileModuleFilters';
        }

        if ($path && is_file($path)) {
          $readFromCache = FALSE;
          $config = $cache->get($path, array($callback), FALSE, $readFromCache);
        }

        break;

      case self::TYPE_DEFAULT_GLOBAL_BEHAVIOR:
      case self::TYPE_DEFAULT_MODULE_BEHAVIOR:
      case self::TYPE_DEFAULT_ACTION_BEHAVIOR:
        if ($type == self::TYPE_DEFAULT_GLOBAL_BEHAVIOR) {
          $callback = 'compileGlobalBehavior';

        } else if ($type == self::TYPE_DEFAULT_MODULE_BEHAVIOR) {
          $callback = 'compileModuleBehavior';

        } else {
          $callback = 'compileActionBehavior';
        }

        if ($path && is_file($path)) {
          $config = $cache->get($path, array($callback));
          $config = self::behaviorLoadReference($config);
        }

        break;

      case self::TYPE_DEFAULT_GLOBAL_HELPERS:
      case self::TYPE_DEFAULT_MODULE_HELPERS:
        if ($type == self::TYPE_DEFAULT_GLOBAL_HELPERS) {
          $callback = 'compileGlobalHelpers';

        } else {
          $callback = 'compileModuleHelpers';
        }

        if ($path && is_file($path)) {
          $readFromCache = FALSE;
          $config = $cache->get($path, array($callback), FALSE, $readFromCache);

          // global_helpers.yml が更新された場合は filters.yml も更新する
          // @see PHI_ConfigCompiler::compileModuleHelpers()
          if (!$readFromCache && $type == self::TYPE_DEFAULT_GLOBAL_HELPERS) {
            $cache->delete(self::getPath(self::TYPE_DEFAULT_MODULE_HELPERS));
          }
        }

        break;

      case self::TYPE_DEFAULT_SITE:
        if (is_file($path)) {
          $config = $cache->get($path, NULL, TRUE);
        }

        break;

      case self::TYPE_DEFAULT_CUSTOM:
        if (is_file($path)) {
          $config = $cache->get($path, NULL, TRUE);
        }

        break;
    }

    return $config;
  }

  /**
   * ポリシーデータを取得します。
   *
   * @param int $type 参照するファイルタイプ。PHI_Config::TYPE_POLICY_* 定数を指定。
   * @return array ポリシーデータを返します。
   */
  private static function getPolicy($type)
  {
    static $compiler;

    if ($compiler === NULL) {
      PHI_CommonUtils::loadVendorLibrary('spyc/spyc.php');
      $compiler = new PHI_ConfigCompiler();
    }

    $config = array();
    $callback = NULL;
    $path = self::getPath($type);

    switch ($type) {
      case self::TYPE_POLICY_APPLICATION:
        $callback = 'compileApplication';
        break;

      case self::TYPE_POLICY_BASE_DICON:
        $callback = 'compileBaseDicon';
        break;

      case self::TYPE_POLICY_ROUTES:
        $callback = 'compileRoutes';
        break;

      case self::TYPE_POLICY_GLOBAL_FILTERS:
        $callback = 'compileGlobalFilters';
        break;

      case self::TYPE_POLICY_GLOBAL_HELPERS:
        $callback = 'compileGlobalHelpers';
        break;
    }

    if ($callback) {
      $arguments = array($path, Spyc::YAMLLoad($path));
      $config = call_user_func_array(array($compiler, $callback), $arguments);
    }

    return $config;
  }

  /**
   * @param array $data
   * @return array
   */
  public static function behaviorLoadReference($data)
  {
    if (isset($data['@ref']) && is_string($data['@ref'])) {
      $data = self::getBehavior($data['@ref'], TRUE);

    } else {
      foreach ($data as $name => $value) {
        if (is_array($value) && isset($value['@ref'])) {
          $include = $value['@ref'];
          $reference = self::getArray(self::TYPE_DEFAULT_ACTION_BEHAVIOR, $include);

          if (isset($reference[$name])) {
            $data[$name] = $reference[$name];
          }
        }
      }
    }

    return $data;
  }
}
