<?php
require PHI_LIBS_DIR . '/controller/action/PHI_Action.php';
require PHI_LIBS_DIR . '/controller/router/PHI_RouteResolver.php';
require PHI_LIBS_DIR . '/controller/filter/PHI_Filter.php';
require PHI_LIBS_DIR . '/controller/filter/PHI_FilterManager.php';
require PHI_LIBS_DIR . '/controller/filter/PHI_FilterChain.php';
require PHI_LIBS_DIR . '/controller/filter/PHI_ActionFilter.php';
require PHI_LIBS_DIR . '/controller/forward/PHI_Forward.php';
require PHI_LIBS_DIR . '/controller/forward/PHI_ForwardStack.php';

/**
 * Web アプリケーションのためのフロントエンドコントローラ機能を提供します。
 *
 * @package controller
 */
class PHI_FrontController extends PHI_Object
{
  /**
   * オブザーバオブジェクト。
   * @var PHI_KernelEventObserver
   */
  private $_observer;

  /**
   * アプリケーション設定。
   * @var PHI_ParameterHolder
   */
  private $_config;

  /**
   *{@link PHI_AppPathManager} オブジェクト。
   * @var PHI_AppPathManager
   */
  private $_pathManager;

  /**
   * {@link PHI_HttpRequest} オブジェクト。
   * @var PHI_HttpRequest
   */
  private $_request;

  /**
   * {@link PHI_HttpResponse} オブジェクト。
   * @var PHI_HttpResponse
   */
  private $_response;

  /**
   * {@link PHI_RouteResolver} オブジェクト。
   * @var PHI_RouteResolver
   */
  private $_resolver;

  /**
   * ルートオブジェクト。
   * @var PHI_Route
   */
  private $_route;

  /**
   * コンストラクタ。
   */
  private function __construct()
  {
    $this->_observer = new PHI_KernelEventObserver(PHI_BootLoader::BOOT_MODE_WEB);
    $this->_observer->initialize();

    $container = PHI_DIContainerFactory::getContainer();

    $this->_request = $container->getComponent('request');
    $this->_response = $container->getComponent('response');

    $this->_config = PHI_Config::getApplication();
    $this->_pathManager = PHI_AppPathManager::getInstance();
    $this->_resolver = new PHI_RouteResolver($this->_request);
  }

  /**
   * フロントコントローラのインスタンスオブジェクトを取得します。
   *
   * @return PHI_FrontController フロントコントローラのインスタンスオブジェクトを返します。
   */
  public static function getInstance()
  {
    static $instance;

    if ($instance === NULL) {
      $instance = new PHI_FrontController();
    }

    return $instance;
  }

  /**
   * オブザーバイブジェクトを取得します。
   *
   * @return PHI_KernelEventObserver オブザーバオブジェクトを返します。
   */
  public function getObserver()
  {
    return $this->_observer;
  }

  /**
   * HTTP リクエストオブジェクトを取得します。
   *
   * @return PHI_HttpRequest HTTP リクエストオブジェクトを返します。
   */
  public function getRequest()
  {
    return $this->_request;
  }

  /**
   * HTTP レスポンスオブジェクトを取得します。
   *
   * @return PHI_HttpResponse HTTP レスポンスオブジェクトを返します。
   */
  public function getResponse()
  {
    return $this->_response;
  }

  /**
   * ルータオブジェクトを取得します。
   *
   * @return PHI_RouteResolver ルータオブジェクトを返します。
   */
  public function getRouter()
  {
    return $this->_resolver;
  }

  /**
   * アクションを実行するための準備を行います。
   *
   * @throws PHI_RequestException リクエストパスが見つからない場合に発生。
   */
  public function dispatch()
  {
    // ルートの探索
    if ($route = $this->_resolver->connect()) {
      $this->_request->setRoute($route);
      $this->_route = $route;

      $this->_observer->dispatchEvent('postRouteConnect');

      ob_start();

      $this->forward($route->getActionName());
      $buffer = ob_get_contents();

      ob_end_clean();

      if (!$this->_response->isCommitted()) {
        $arguments = array(&$buffer);
        $this->_observer->dispatchEvent('preOutput', $arguments);

        $this->_response->write($buffer);
        $this->_response->flush();
      }

      $this->_observer->dispatchEvent('postProcess');

    // ルートが見つからない場合は 404 ページを出力
    } else {
      $this->_response->sendError(404);
    }
  }

  /**
   * アクションのフォワードを実行します。
   *
   * @param string $actionName フォワードするアクション名。
   * @throws PHI_ForwardException フォワードが失敗した場合に発生。
   */
  public function forward($actionName, $validate = TRUE)
  {
    $moduleName = $this->_route->getModuleName();

    // 実行モジュールを決定する
    $modulePath = $this->getModulePath($moduleName);

    if ($modulePath) {
      $actionClass = $this->loadAction($actionName, $moduleName, $modulePath, $validate);

      if ($actionClass) {
        $filter = new PHI_FilterManager();
        $filter->addFilter('actionFilter', array('class' => 'PHI_ActionFilter'));
        $filter->doFilters();

      } else {
        $this->_response->sendError(404);
      }

    // モジュールが存在しない場合
    } else {
      $this->_response->sendError(404);
    }
  }

  /**
   * モジュールのパスを取得します。
   *
   * @param string $moduleName モジュール名。
   * @return string モジュール名に対応するパスを返します。
   */
  private function getModulePath($moduleName)
  {
    if ($moduleName === 'cpanel') {
      $modulePath = PHI_ROOT_DIR . '/webapps/cpanel/modules/cpanel';
      $this->_pathManager->addModulePath('cpanel', $modulePath);

    } else {
      $modulePath = $this->_pathManager->getModulePath($moduleName);
    }

    if (!is_dir($modulePath)) {
      $modulePath = FALSE;
    }

    return $modulePath;
  }

  /**
   * アクションオブジェクトを取得します。
   *
   * @return PHI_Action アクションオブジェクトを返します。
   */
  private function loadAction($actionName, $moduleName, $modulePath, $validate)
  {
    static $loads = array();

    $action = FALSE;

    if (!isset($loads[$actionName])) {
      $actionClassName = $actionName . 'Action';
      $searchBasePath = $modulePath . '/actions';
      $actionPath = PHI_ClassLoader::findPath($actionClassName, $searchBasePath);

      if ($actionPath !== FALSE) {
        $actionRelativePath = substr($actionPath, strpos($actionPath, 'actions') + 7);
        $packagePath = dirname(substr($actionRelativePath, 1));

        if (DIRECTORY_SEPARATOR === "\\") {
          $packageName = str_replace("\\", '/', $padckagePath);
        } else {
          $packageName = $packagePath;
        }

        if ($packageName === '.') {
          $packageName = $moduleName . ':/';
          $behaviorRelativePath = $actionName . '.yml';

        } else {
          $packageName = $moduleName . ':' . $packageName;
          $behaviorRelativePath = sprintf('%s%s%s.yml', $packagePath, DIRECTORY_SEPARATOR, $actionName);
        }

        if ($this->_resolver->isAllowPackage($packageName)) {
          require $actionPath;

          // アクションクラスの生成
          $actionClass = $actionName . 'Action';
          $behaviorPath = $this->_pathManager->getModuleBehaviorsPath($moduleName, $behaviorRelativePath);

          $action = new $actionClass($actionPath, $behaviorPath);
          $action->setPackageName($packageName);
          $action->setValidate($validate);

          $forward = new PHI_Forward($moduleName, $actionName);
          $forward->setAction($action);
          $this->_route->getForwardStack()->add($forward);

          $config = PHI_Config::getBehavior($actionName);
          $action->setRoles($config->getArray('roles'));
        }

        $loads[$actionName] = $action;
      }

    } else {
      $action = $loads[$actionName];
    }

    return $action;
  }
}
