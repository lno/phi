<?php
/**
 * @package actions
 */
class AnalyzeActionDetailAction extends PHI_Action
{
  public function execute()
  {
    $request = $this->getRequest();
    $view = $this->getView();

    $moduleName = $request->getQuery('module', NULL, TRUE);
    $actionName = $request->getQuery('action');
    $from = $request->getQuery('from');
    $to = $request->getQuery('to');

    $hash = hash('md5', $moduleName . $actionName);
    $view->setAttribute('hash', $hash);

    $actionRequestsDAO = PHI_DAOFactory::create('PHI_ActionRequests');

    $slowRequests = $actionRequestsDAO->findSlowRequests($moduleName, $actionName, $from, $to);
    $view->setAttribute('slowRequests', $slowRequests);

    // 遅いステートメントの抽出
    $sqlRequestsDAO = PHI_DAOFactory::create('PHI_SQLRequestsDAO');
    $slowStatements = $sqlRequestsDAO->findSlowStatementByAction($moduleName, $actionName, $from, $to);
    $view->setAttribute('slowStatements', $slowStatements);

    return PHI_View::SUCCESS;
  }
}
