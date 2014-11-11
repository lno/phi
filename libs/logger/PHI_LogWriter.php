<?php
/**
 * ローテート機能を備えたログの出力機能を提供します。
 *
 * @package logger
 */
class PHI_LogWriter extends PHI_FileWriter
{
  /**
   * @var PHI_LogRotateExecutor
   */
  private $_logRotateExecutor;

  /**
   * コンストラクタ。
   *
   * @param PHI_LogRotatePolicy $rotatePattern ローテートパターンのインスタンス。
   * @param bool $appendMode {PHI_FileWriter::__construct()} メソッドを参照。
   */
  public function __construct(PHI_LogRotatePolicy $rotatePattern, $appendMode = TRUE)
  {
    $writePath = $rotatePattern->getWritePath();
    $executorClassName = $rotatePattern->getExecutorClassName();
    $this->_logRotateExecutor = new $executorClassName($writePath, $rotatePattern);

    parent::__construct($writePath, $appendMode);
  }

  /**
   * 全てのローテートログファイルを削除します。
   */
  public function deleteRotateLogs()
  {
    $logs = $this->_logRotateExecutor->getRotateLogs();

    foreach ($logs as $path) {
      if (is_file($path)) {
        unlink($path);
      }
    }

    $this->clear();
  }

  /**
   * デストラクタ。
   */
  public function __destruct()
  {
    try {
      if ($this->_logRotateExecutor->isRotateRequired()) {
        $this->_logRotateExecutor->rotate();
      }

      $this->flush();

    } catch (Exception $e) {
      PHI_ExceptionStackTraceDelegate::invoker($e);

      die();
    }
  }
}
