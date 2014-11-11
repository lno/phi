<?php
/**
 * 日付によるログローテートを処理します。
 *
 * @package logger.rotator.executor
 */
class PHI_LogRotateDateBasedExecutor extends PHI_LogRotateExecutor
{
  /**
   * @var array
   */
  private $_rotateLogs = array();

  /**
   * @see PHI_LogRotatePolicy::isRotateRequired()
   */
  public function isRotateRequired()
  {
    $result = FALSE;
    $generation = $this->_logRotatePolicy->getGeneration();

    if ($generation != PHI_LogRotatePolicy::GENERATION_UNLIMITED) {
      $this->_rotateLogs = $this->getRotateLogs();

      if (sizeof($this->_rotateLogs) > $generation) {
        $result = TRUE;
      }
    }

    return $result;
  }

  /**
   * @see PHI_LogRotatePolicy::rotate()
   */
  public function rotate()
  {
    $generation = $this->_logRotatePolicy->getGeneration();

    if ($generation != PHI_LogRotatePolicy::GENERATION_UNLIMITED) {
      $j = sizeof($this->_rotateLogs) - $generation;

      for ($i = 0; $i < $j; $i++) {
        if (is_file($this->_rotateLogs[$i])) {
          unlink($this->_rotateLogs[$i]);
        }
      }
    }
  }
}
