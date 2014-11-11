<?php
/**
 * @package actions
 */
class AnalyzeSQLDeleteAction extends PHI_Action
{
  public function execute()
  {
    $hash = $this->getRequest()->getQuery('hash');

    $sqlRequestsDAO = PHI_DAOFactory::create('PHI_SQLRequestsDAO');
    $sqlRequestsDAO->deleteByStatementHash($hash);

    return PHI_View::NONE;
  }
}
