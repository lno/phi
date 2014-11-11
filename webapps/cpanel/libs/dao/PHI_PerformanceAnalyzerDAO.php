<?php
/**
 * This class was generated automatically by DAO Generator.
 *
 * @package libs.dao
 */
class PHI_PerformanceAnalyzerDAO extends PHI_DAO
{
  public function __construct()
  {
    $dataSourceId = PHI_PerformanceListener::getDataSourceId();
    $this->setDataSourceId($dataSourceId);
  }
}
