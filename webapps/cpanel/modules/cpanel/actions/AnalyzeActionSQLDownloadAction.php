<?php
/**
 * @package actions
 */
class AnalyzeActionSQLDownloadAction extends PHI_Action
{
  public function execute()
  {
    $actionRequestId = $this->getRequest()->getQuery('actionRequestId');

    $sqlRequestsDAO = PHI_DAOFactory::create('PHI_SQLRequests');
    $sqlRequests = $sqlRequestsDAO->findByActionRequestId($actionRequestId);

    $header = array('ID', 'Statement type', 'Statement', 'Process time', 'Class', 'Function', 'File path', 'Line');
    $i = 1;

    $csv = new PHI_CSVWriter();
    $csv->setHeader($header);

    foreach ($sqlRequests as $sqlRequest) {
      $array = array($i++,
        $sqlRequest['statement_type_name'],
        $sqlRequest['statement'],
        $sqlRequest['process_time'],
        $sqlRequest['class'],
        $sqlRequest['function'] . '()',
        $sqlRequest['file_path'],
        $sqlRequest['line']);

      $csv->addRecord($array);
    }

    $csv->download(sprintf('report-%s.csv', date('Ymd_His')));

    return PHI_View::NONE;
  }
}
