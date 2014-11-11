<?php
/**
 * @package actions
 */
class AnalyzeSQLReportAction extends PHI_Action
{
  public function execute()
  {
    $request = $this->getRequest();

    $moduleName = $request->getQuery('target', NULL, TRUE);
    $from = $request->getQuery('from');
    $to = $request->getQuery('to');

    $sqlRequestsDAO = PHI_DAOFactory::create('PHI_SQLRequestsDAO');
    $dailySummary = $sqlRequestsDAO->getDailySummary($moduleName, $from, $to);

    $this->getView()->setAttribute('dailySummary', $dailySummary);

    return PHI_View::SUCCESS;
  }
}
