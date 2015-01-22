<?php
/**
 * @package rc.unit
 */
class DatabaseReader
{
  protected $_limitPageCount = 6000000;
  protected $_sql = NULL;
  protected $_totalCount = NULL;
  protected $_totalPageCount = NULL;
  protected $_currentPageCount = 0;
  /**
   * @var PHI_DatabaseResultSet|null
   */
  protected $_currentData = NULL;
  protected $_currentIndex = NULL;
  protected $_bindValues = NULL;

  public function open($sql, $firstPageCount = 1)
  {
    $this->clear();
    $this->_sql = $sql;
    $this->_currentPageCount = $firstPageCount - 1;
    if ($this->_currentPageCount < 0) {
      $this->_currentPageCount = 0;
    }
    $this->_bindValues = [];
  }

  public function close()
  {
    $this->clear();
  }

  public function clear()
  {
    $this->_sql = NULL;
    $this->_totalCount = NULL;
    $this->_totalPageCount = NULL;
    $this->_currentPageCount = NULL;
    if ($this->_currentData) {
      $this->_currentData->close();
    }
    $this->_currentData = NULL;
    $this->_bindValues = NULL;
  }

  public function setLimitPageCount($limitPageCount)
  {
    $this->_limitPageCount = $limitPageCount;
  }

  public function bindValue($key, $value)
  {
    $this->_bindValues[$key] = $value;
  }

  /**
   * @return null|PHI_RecordObject
   */
  public function next($pageBreakFunction = '')
  {
    $record = $this->_currentData ? $this->_currentData->read() : NULL;
    if (!$record) {
      if (++$this->_currentPageCount <= $this->getTotalPageCount()) {
        $offset = $this->_limitPageCount * ($this->_currentPageCount - 1);
        $sql = sprintf('%s LIMIT %d OFFSET %d', $this->_sql, $this->_limitPageCount, $offset);
        $this->_currentData = $this->executeQuery($sql);

        $record = $this->_currentData->read();
        if ($pageBreakFunction) {
          $pageBreakFunction();
        }
      } else {
        $this->_currentData = NULL;
        $record = NULL;
      }
    }

    return $record;
  }

  public function getCurrentPageCount()
  {
    return $this->_currentPageCount;
  }

  public function getTotalPageCount()
  {
    if ($this->_totalPageCount === NULL) {
      $totalCount = $this->getTotalRecordCount();
      $this->_totalPageCount = ceil($totalCount / $this->_limitPageCount);
    }

    return $this->_totalPageCount;
  }

  public function getTotalRecordCount()
  {
    if ($this->_totalCount === NULL) {
      $sql = sprintf('SELECT COUNT(*) count FROM (%s) tmp', $this->_sql);
      $rs = $this->executeQuery($sql);
      $record = $rs->read();
      $this->_totalCount = $record ? $record->count : 0;
    }

    return $this->_totalCount;
  }

  /**
   * @param $sql
   * @return PHI_DatabaseResultSet
   */
  protected function executeQuery($sql)
  {
    $result = NULL;

    $retryCount = 0;
    $isSuccess = FALSE;
    while (!$isSuccess && $retryCount <= 10) {
      try {
        $ps = DI::Database()->getConnection()->createStatement($sql);
        if (empty($this->_bindValues)) {
          $result = $ps->executeQuery();
        } else {
          foreach ($this->_bindValues as $key => $value) {
            $ps->bindValue($key, $value);
          }
          $result = $ps->executeQuery();
        }
        $isSuccess = TRUE;
      } catch (Exception $e) {
        $message = "SQL Error ($retryCount): sql = $sql";
        LogUtil::error($message.' :: '.$e->getMessage());
        LogUtil::error('Current Pager Count: '.$this->_currentPageCount);
        if ($retryCount % 10 == 0 || $retryCount % 10 == 3 || $retryCount % 10 == 5) {
          MailUtil::sendNotice('SQL Connection Error', array(
            $message,
            '',
            '================',
            'Exception Detail',
            '================',
            $e->getMessage()
          ));
        }
        $retryCount++;
        $isSuccess = FALSE;
        DI::Database()->closeAll();
        sleep(60);
      }
      if (!$isSuccess) {
        throw new PHI_Exception('SQL Connection Error');
      }
    }

    return $result;
  }
}
