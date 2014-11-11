<?php
/**
 * フィルタの動作を定義する抽象クラスです。
 *
 * global_filters.yml の設定例:
 * <code>
 * {フィルタ ID}
 *   # フィルタクラス名。
 *   class:
 *
 *   # フィルタを起動するかどうか。
 *   enable: TRUE
 * </code>
 *
 * @package controller.filter
 */
abstract class PHI_Filter extends PHI_WebApplication
{
  /**
   * @var string
   */
  protected $_filterId;

  /**
   * @var PHI_ParameterHolder
   */
  protected $_holder;

  /**
   * コンストラクタ。
   *
   * @param string $filterId フィルタ ID。
   * @param PHI_ParameterHolder $holder パラメータホルダ。
   */
  public function __construct($filterId, PHI_ParameterHolder $holder)
  {
    parent::__construct();

    $this->_filterId = $filterId;
    $this->_holder = $holder;
  }

  /**
   * フロントコントローラオブジェクトを取得します。
   *
   * @return PHI_FrontController オブジェクトを返します。
   */
  public function getController()
  {
    return PHI_FrontController::getInstance();
  }

  /**
   * フィルタの動作を実装します。
   *
   * @param PHI_ParameterHolder $holder パラメータホルダ。
   */
  abstract public function doFilter(PHI_FilterChain $chain);
}
