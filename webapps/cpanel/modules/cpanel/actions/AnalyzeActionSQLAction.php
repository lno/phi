<?php
/**
 * @package actions
 */
class AnalyzeActionSQLAction extends PHI_Action
{
  public function execute()
  {
    $actionRequestId = $this->getRequest()->getQuery('actionRequestId');

    $sqlRequestsDAO = PHI_DAOFactory::create('PHI_SQLRequests');
    $sqlRequests = $sqlRequestsDAO->findByActionRequestId($actionRequestId);

    $this->getView()->setAttribute('sqlRequests', $sqlRequests);

    return PHI_View::SUCCESS;
  }
}
