<?php
/**
 * @package actions
 */
class AnalyzeSQLAction extends PHI_Action
{
  public function execute()
  {
    $request = $this->getRequest();

    $moduleName = $request->getQuery('target', NULL, TRUE);
    $type = $request->getQuery('type');
    $from = $request->getQuery('from');
    $to = $request->getQuery('to');
    $defaultOrderIndex = '3';

    $sqlRequestsDAO = PHI_DAOFactory::create('PHI_SQLRequestsDAO');

    if ($type === 'default') {
      $orderIndex = $request->getQuery('orderBySqlDefault', $defaultOrderIndex);
      $orderType = $this->getOrderType($orderIndex);
      $slowQueries = $sqlRequestsDAO->findSlowStatement($moduleName, $from, $to, $orderType);

    } else {
      $orderIndex = $request->getQuery('orderBySqlPrepared', $defaultOrderIndex);
      $orderType = $this->getOrderType($orderIndex);
      $slowQueries = $sqlRequestsDAO->findSlowPreparedStatement($moduleName, $from, $to, $orderType);
    }

    $view = $this->getView();
    $view->setAttribute('slowQueries', $slowQueries);
    $view->setAttribute('orderName', 'orderBySql' . ucfirst($type));
    $view->setAttribute('orderIndex', $orderIndex);

    return PHI_View::SUCCESS;
  }

  private function getOrderType($orderIndex)
  {
    $result = NULL;

    // 平均時間ソート
    if ($orderIndex === '3') {
      $result = 'averageTime';

    // 実行回数ソート
    } else if ($orderIndex === '2') {
      $result = 'manyQuery';
    }

    return $result;
  }
}
