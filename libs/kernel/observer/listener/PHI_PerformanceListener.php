<?php
/**
 * このクラスは、実験的なステータスにあります。
 * これは、この関数の動作、関数名、ここで書かれていること全てが phi の将来のバージョンで予告な>く変更される可能性があることを意味します。
 * 注意を喚起するとともに自分のリスクでこのクラスを使用してください。
 *
 * @package kernel.observer.listener
 */
class PHI_PerformanceListener extends PHI_WebApplicationEventListener
{
  /**
   * @var int
   */
  private $_startTime;

  /**
   * @var PHI_SQLProfiler
   */
  private $_profiler;

  /**
   * @see PHI_ApplicationEventListener::getListenEvents()
   */
  public function getListenEvents()
  {
    return array('preProcess', 'postProcess');
  }

  /**
   * @see PHI_ApplicationEventListener::getBootMode()
   */
  public function getBootMode()
  {
    return PHI_BootLoader::BOOT_MODE_WEB;
  }

  /**
   * @see PHI_KernelEventObserver::preProcess()
   */
  public function preProcess()
  {
    $this->_profiler = PHI_DatabaseManager::getInstance()->getProfiler();
    $this->_profiler->start();
    $this->_startTime = microtime(TRUE);

    PHI_ClassLoader::addSearchPath(PHI_ROOT_DIR . '/webapps/cpanel/libs');
  }

  /**
   */
  public static function getDataSourceId()
  {
    $listenersConfig = PHI_Config::get(PHI_Config::TYPE_DEFAULT_APPLICATION)->get('observer.listeners');
    $dataSourceId = FALSE;

    if ($listenersConfig) {
      foreach ($listenersConfig as $listenerId => $attributes) {
        if ($listenerId === 'performanceListener') {
          $dataSourceId = $attributes->get('dataSource', PHI_DatabaseManager::DEFAULT_DATASOURCE_ID);
          break;
        }
      }
    }

    return $dataSourceId;
  }

  /**
   * @see PHI_KernelEventObserver::postProcess()
   */
  public function postProcess()
  {
    if ($this->_profiler) {
      $endTime = microtime(TRUE);
      $reporter = PHI_SQLProfiler::getInstance();

      try {
        $processTime = PHI_NumberUtils::roundDown($endTime - $this->_startTime, 3);
        $controller = PHI_FrontController::getInstance();

        $request = $controller->getRequest();
        $session = $request->getSession();
        $route = $request->getRoute();

        if ($session->isActive()) {
          $sessionId = $session->getId();
        } else {
          $sessionId = NULL;
        }

        // ActionRequest の生成
        $report = array(
          'hostname' => php_uname('n'),
          'sessionId' => $sessionId,
          'requestPath' => $request->getURI(FALSE),
          'moduleName' => $route->getModuleName(),
          'actionName' => $route->getActionName(),
          'selectCount' => $reporter->getSelectCount(),
          'insertCount' => $reporter->getInsertCount(),
          'updateCount' => $reporter->getUpdateCount(),
          'deleteCount' => $reporter->getDeleteCount(),
          'otherCount' => $reporter->getOtherCount(),
          'processTime' => $processTime,
          'summaryDate' => new PHI_DatabaseExpression('CURDATE()'),
          'registerDate' => new PHI_DatabaseExpression('NOW()')
        );
        $actionRequest = new PHI_ActionRequests($report);

        $actionRequestsDAO = PHI_DAOFactory::create('PHI_ActionRequestsDAO');
        $actionRequestId = $actionRequestsDAO->insert($actionRequest);

        // SQLRequest の生成
        $reports = $reporter->getReports();
        $sqlRequestsDAO = PHI_DAOFactory::create('PHI_SQLRequestsDAO');

        $phiRootDirectory = str_replace('/', DIRECTORY_SEPARATOR, PHI_ROOT_DIR);

        foreach ($reports as $report) {
          if (strpos($report->fileName, $phiRootDirectory) !== FALSE) {
            continue;
          }

          $sqlRequest = new PHI_SqlRequests();
          $sqlRequest->actionRequestId = $actionRequestId;

          switch ($report->statementType) {
            case 'select':
              $statementType = PHI_SQLRequestsDAO::STATEMENT_TYPE_SELECT;
              break;

            case 'insert':
              $statementType = PHI_SQLRequestsDAO::STATEMENT_TYPE_INSERT;
              break;

            case 'update':
              $statementType = PHI_SQLRequestsDAO::STATEMENT_TYPE_UPDATE;
              break;

            case 'delete':
              $statementType = PHI_SQLRequestsDAO::STATEMENT_TYPE_DELETE;
              break;

            default:
              $statementType = PHI_SQLRequestsDAO::STATEMENT_TYPE_OTHER;
              break;
          }

          $sqlRequest->statementType = $statementType;
          $sqlRequest->statementHash = $report->statementHash;

          if (isset($report->preparedStatement)) {
            $sqlRequest->preparedStatement = $report->preparedStatement;
          } else {
            $sqlRequest->preparedStatement = PHI_DatabaseExpression::null();
          }

          $sqlRequest->statement = $report->statement;
          $sqlRequest->processTime = $report->time;
          $sqlRequest->filePath = $report->fileName;
          $sqlRequest->className = $report->className;
          $sqlRequest->methodName = $report->methodName;
          $sqlRequest->line = $report->line;

          $sqlRequestsDAO->insert($sqlRequest);
        }

      } catch (PDOException $e) {
        PHI_ErrorHandler::invokeFatalError(E_ERROR,
          $e->getMessage(),
          __FILE__,
          __LINE__);
      }
    }
  }
}
