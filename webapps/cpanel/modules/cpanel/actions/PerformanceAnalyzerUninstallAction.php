<?php
/**
 * @package actions
 */
class PerformanceAnalyzerUninstallAction extends PHI_Action
{
  public function execute()
  {
    $this->getObserver()->removeEventListener('PHI_PerformanceListener');
    $dataSourceId = PHI_PerformanceListener::getDataSourceId();

    $conn = $this->getDatabase()->getConnection($dataSourceId);
    $command = $conn->getCommand();
    $daos = array('PHI_ActionRequestsDAO', 'PHI_SQLRequestsDAO');

    foreach ($daos as $dao) {
      $tableName = PHI_DAOFactory::create($dao)->getTableName();

      if ($command->existsTable($tableName)) {
        $command->dropTable($tableName);
      }
    }

    return PHI_View::SUCCESS;
  }
}
