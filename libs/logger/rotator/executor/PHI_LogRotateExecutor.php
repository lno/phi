<?php
/**
 * ログのローテート方法を定義します。
 *
 * @package logger.rotator.executor
 */
abstract class PHI_LogRotateExecutor extends PHI_Object
{
  /**
   * @var string
   */
  protected $_path;

  /**
   * @var PHI_LogRotatePolicy
   */
  protected $_logRotatePolicy;

  /**
   * コンストラクタ。
   *
   * @param string $path ログの出力先。絶対パス、または ({APP_ROOT_DIR}/logs からの) 相対パスでの指定が可能。
   * @param PHI_LogRotatePolicy $logRotatePolicy ローテートパターンのインスタンス。
   */
  public function __construct($path, $logRotatePolicy)
  {
    $this->_path = $path;
    $this->_logRotatePolicy = $logRotatePolicy;
  }

  /**
   * ローテートログのファイルリストを取得します。
   *
   * @return array 自然順アルゴリズムでソートされたファイルリストを返します。
   */
  public function getRotateLogs()
  {
    $rotateLogs = glob(sprintf('%s.*', $this->_logRotatePolicy->getBasePath()), GLOB_NOSORT);
    array_multisort($rotateLogs, SORT_NATURAL);

    return $rotateLogs;
  }

  /**
   * ログファイルのローテートが必要な状態にあるかどうか取得します。
   *
   * @return bool ローテートが必要な場合は TRUE、不要な場合は FALSE を返します。
   */
  abstract public function isRotateRequired();

  /**
   * ログのローテートを行います。
   */
  abstract public function rotate();
}
