<?php
/**
 * グローバルフィルタ、及びモジュールフィルタを管理し、適切な {@link PHI_FilterChain} 構造を生成します。
 *
 * @package controller.filter
 */
class PHI_FilterManager extends PHI_Object
{
  /**
   * フィルタリスト。
   * @var array
   */
  private $_filters = array();

  /**
   * コンストラクタ。
   */
  public function __construct()
  {
    $config = PHI_Config::getFilters();

    foreach ($config as $filterName => $attributes) {
      $this->addFilter($filterName, $attributes->toArray());
    }
  }

  /**
   * フィルタマネージャにフィルタを登録します。
   *
   * @param string $filterId フィルタ ID。
   * @param array $attributes フィルタ属性。
   */
  public function addFilter($filterId, array $attributes = array())
  {
    if (PHI_ArrayUtils::find($attributes, 'enable', TRUE)) {
      $this->_filters[$filterId] = $attributes;
    }
  }

  /**
   * 指定したフィルタ名が登録されているかチェックします。
   *
   * @param string $filterName チェックするフィルタ名。
   * @return bool フィルタが登録済みかどうかを TRUE/FALSE で返します。
   */
  public function hasFilter($filterName)
  {
    return isset($this->_filters[$filterName]);
  }

  /**
   * PHI_FilterManager に登録されているグローバルフィルタ、モジュールフィルタから {@link PHI_FilterChain} を生成し、フィルタの処理を実行します。
   */
  public function doFilters()
  {
    $filterChain = new PHI_FilterChain($this->_filters);
    $filterChain->filterChain();
  }
}
