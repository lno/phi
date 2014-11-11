<?php
/**
 * @package actions
 */
class AnalyzeSettingFormAction extends PHI_Action
{
  public function execute()
  {
    $view = $this->getView();
    $actionRequestsDAO = PHI_DAOFactory::create('PHI_ActionRequests');

    // データ期間の取得
    $beginDate = $actionRequestsDAO->getBeginSummary();
    $view->setAttribute('beginDate', $beginDate);

    $endDate = $actionRequestsDAO->getEndSummary();
    $view->setAttribute('endDate', $endDate);

    // データサイズの取得
    $dataSourceId = PHI_PerformanceListener::getDataSourceId();
    $tableNames = array('phi_action_requests', 'phi_sql_requests');
    $dataList = array();
    $command = $this->getDatabase()->getConnection($dataSourceId)->getCommand();

    foreach ($tableNames as $tableName) {
      $dataList[$tableName] = array(
        'count' => $command->getRecordCount($tableName),
        'size' => $command->getTableSize($tableName)
      );
    }

    $view->setAttribute('dataList', $dataList);

    return PHI_View::SUCCESS;
  }
}
