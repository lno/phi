<?php
/**
 * ローテートしないポリシーを定義します。
 *
 * @package logger.rotator.policy
 */
class PHI_LogRotateNullBasedPolicy extends PHI_LogRotatePolicy
{
  /**
   * @see PHI_LogRotatePolicy::getExecutorClassName()
   */
  public function getExecutorClassName()
  {
    return 'PHI_LogRotateNullBasedExecutor';
  }
}
