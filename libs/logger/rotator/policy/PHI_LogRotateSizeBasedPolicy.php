<?php
/**
 * PHI_LogRotateSizeBasedPolicy のローテートを処理します。
 *
 * @package logger.rotator.policy
 */
class PHI_LogRotateSizeBasedPolicy extends PHI_LogRotatePolicy
{
  /**
   * @var int
   */
  private $_maxSize = 10485760;

  /**
   * ファイルサイズの上限を設定します。
   * バイト数による指定のほか、'1024KB'、'10MB' といった単位を含めたサイズ指定も可能です。
   * 既定の上限サイズは 10485760 (10 MB) となります。
   *
   * @param mixed $maxSize ファイルサイズの上限。
   *   指定可能な形式は {@link PHI_NumberUtils::realBytes()} を参照。
   */
  public function setMaxSize($maxSize)
  {
    if (is_string($maxSize)) {
      $maxSize = PHI_NumberUtils::realBytes($maxSize);
    }

    $this->_maxSize = $maxSize;
  }

  /**
   * ファイルサイズの上限を取得します。
   *
   * @return int ファイルサイズの上限を返します。
   */
  public function getMaxSize()
  {
    return $this->_maxSize;
  }

  /**
   * @see PHI_LogRotatePolicy::getExecutorClassName()
   */
  public function getExecutorClassName()
  {
    return 'PHI_LogRotateSizeBasedExecutor';
  }
}
