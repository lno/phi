<?php
/**
 * @package actions
 */
class PerformanceAnalyzerAction extends PHI_Action
{
  public function execute()
  {
    // パフォーマンスアナライザがインストールされているかチェック
    $config = PHI_Config::get(PHI_Config::TYPE_DEFAULT_APPLICATION);
    $dataSourceId = PHI_PerformanceListener::getDataSourceId();
    $hasInstall = FALSE;

    if ($dataSourceId) {
      $command = $this->getDatabase()->getConnection($dataSourceId)->getCommand();
      $tableName = PHI_DAOFactory::create('PHI_ActionRequests')->getTableName();

      if ($command->existsTable($tableName)) {
        $hasInstall = TRUE;
      }
    }

    if ($hasInstall) {
      $modules = array();
      $modules[''] = '全てのモジュール';

      foreach (PHI_CoreUtils::getModuleNames() as $module) {
        $modules[$module] = $module;
      }

      $this->getView()->setAttribute('modules', $modules);
      $form = $this->getForm();

      if (!$form->hasName('search')) {
        $from = date('Y-m-d', strtotime('-6 day'));
        $form->set('from', $from);

        $to = date('Y-m-d');
        $form->set('to', $to);
      }

    } else {
      $this->getController()->forward('PerformanceAnalyzerInstallForm');

      return PHI_View::NONE;
    }

    return PHI_View::SUCCESS;
  }
}
