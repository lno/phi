<?php
/**
 * @package actions
 */
class AnalyzeDataResetAction extends PHI_Action
{
  public function execute()
  {
    $sqlRequestsDAO = PHI_DAOFactory::create('PHI_SQLRequestsDAO');
    $sqlRequestsDAO->truncate();

    $actionRequestsDAO = PHI_DAOFactory::create('PHI_ActionRequestsDAO');
    $actionRequestsDAO->truncate();

    echo '1';

    return PHI_View::NONE;
  }
}
