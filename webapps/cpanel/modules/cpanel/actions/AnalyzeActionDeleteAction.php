<?php
/**
 * @package actions
 */
class AnalyzeActionDeleteAction extends PHI_Action
{
  public function execute()
  {
    $request = $this->getRequest();
    $moduleName = $request->getQuery('module');
    $actionName = $request->getQuery('action');

    $actionRequestsDAO = PHI_DAOFactory::create('PHI_ActionRequestsDAO');
    $actionRequestsDAO->deleteByModuleAndAction($moduleName, $actionName);

    return PHI_View::NONE;
  }
}
