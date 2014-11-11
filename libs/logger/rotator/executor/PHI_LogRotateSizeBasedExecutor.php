<?php
/**
 * ファイルサイズによるローテートを処理します。
 *
 * @package logger.rotator.executor
 */
class PHI_LogRotateSizeBasedExecutor extends PHI_LogRotateExecutor
{
  /**
   * @see PHI_LogRotatePolicy::isRotateRequired()
   */
  public function isRotateRequired()
  {
    clearstatcache(FALSE, $this->_path);
    $maxSize = $this->_logRotatePolicy->getMaxSize();

    if (is_file($this->_path) && filesize($this->_path) >= $maxSize) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * @see PHI_LogRotatePolicy::rotate()
   */
  public function rotate()
  {
    $logs = $this->getRotateLogs();
    $generation = $this->_logRotatePolicy->getGeneration();

    if ($j = sizeof($logs)) {
      for ($i = $j; $i > 0; $i--) {
        $oldPath = $logs[$i - 1];

        if (!is_file($oldPath)) {
          continue;
        }

        $oldIndex = substr($oldPath, strrpos($oldPath, '.') + 1);

        if ($generation != PHI_LogRotatePolicy::GENERATION_UNLIMITED && $generation - 1 <= $oldIndex) {
          unlink($oldPath);

        } else {
          $newPath = sprintf('%s.%d', $this->_path, $oldIndex + 1);
          rename($oldPath, $newPath);
        }
      }
    }

    rename($this->_path, sprintf('%s.1', $this->_path));
  }
}
