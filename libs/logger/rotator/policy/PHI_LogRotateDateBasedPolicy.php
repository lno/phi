<?php
/**
 * PHI_LogRotateDateBasedPolicy のローテートを処理します。
 *
 * @package logger.rotator.policy
 */
class PHI_LogRotateDateBasedPolicy extends PHI_LogRotatePolicy
{
  /**
   * 月次ローテート。
   */
  const PATTERN_MONTHLY = 'Y-m';

  /**
   * 週次ローテート。
   */
  const PATTERN_WEEKLY = 'Y-W';

  /**
   * 日次ローテート。
   */
  const PATTERN_DAILY = 'Y-m-d';

  /**
   * 毎時ローテート。
   */
  const PATTERN_HOURLY = 'Y-m-d-H';

  /**
   * @see PHI_LogRotatePolicy::$_generation
   */
  protected $_generation = parent::GENERATION_UNLIMITED;

  /**
   * @var string
   */
  private $_datePattern = self::PATTERN_DAILY;

  /**
   * @see PHI_LogRotatePolicy::getWritePath()
   */
  public function getWritePath()
  {
    return sprintf('%s.%s', $this->_basePath, date($this->_datePattern));
  }

  /**
   * ファイル名に付加する日付のフォーマットを設定します。
   * PHI_LogRotateDateBasedPolicy::PATTERN_* 定数を指定、または {@link date()} が識別可能なフォーマットを指定することができます。
   * 例えばファイル名が 'error'、フォーマットが PHI_LogRotateDateBasedPolicy::PATTERN_DAILY の場合、出力対象のファイル名は 'error.1980-08-06' となります。
   * フォーマットが未指定の場合は日次ローテートが有効になります。
   *
   * @param string $datePattern 日付のフォーマット。
   */
  public function setDatePattern($datePattern)
  {
    $this->_datePattern = $datePattern;
  }

  /**
   * ファイル名に付加する日付のフォーマットを取得します。
   *
   * @return string ファイル名に付加する日付のフォーマットを返します。
   */
  public function getDatePattern()
  {
    return $this->_datePattern;
  }

  /**
   * @see PHI_LogRotatePolicy::getExecutorClassName()
   */
  public function getExecutorClassName()
  {
    return 'PHI_LogRotateDateBasedExecutor';
  }
}
