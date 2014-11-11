<?php
/**
 * PHI_LogRotateNullBasedPolicy のローテートを処理します。
 *
 * @package logger.rotator.executor
 */
class PHI_LogRotateNullBasedExecutor extends PHI_LogRotateExecutor
{
  /**
   * @see PHI_LogRotatePolicy::isRotateRequired()
   */
  public function isRotateRequired()
  {
    return FALSE;
  }

  /**
   * @see PHI_LogRotatePolicy::rotate()
   */
  public function rotate()
  {}
}
