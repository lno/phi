<?php
/**
 * フィルタの順序集合を保持し、global_filters.yml、filters.yml に定義された順序でフィルタを実行します。
 *
 * @package controller.filter
 */
class PHI_FilterChain extends PHI_Object
{
  /**
   * @var array
   */
  private $_filters;

  /**
   * コンストラクタ。
   *
   * @param array $filters 実行するフィルタのリスト。
   */
  public function __construct(array $filters)
  {
    $this->_filters = $filters;
  }

  /**
   * PHI_FilterManager に登録されているフィルタを順次実行します。
   * PHI_FilterChain は、プリフィルタ実行後、対象となるアクションを実行し、最後にポストフィルタを実行します。
   */
  public function filterChain()
  {
    $execute = FALSE;
    $filterId = key($this->_filters);

    if ($filterId === NULL) {
      return;
    }

    $attributes = $this->_filters[$filterId];
    next($this->_filters);

    $forward = PHI_ArrayUtils::find($attributes, 'forward', FALSE);
    $route = PHI_FrontController::getInstance()->getRequest()->getRoute();
    $forwardStack = $route->getForwardStack();

    if (!$forward && $forwardStack->getSize() > 1) {
      end($this->_filters);
      $filterId = key($this->_filters);
      $attributes = $this->_filters[$filterId];

      next($this->_filters);
    }

    if (isset($attributes['packages'])) {
      $packageName = $forwardStack->getLast()->getPackageName();
      $execute = PHI_Action::isValidPackage($packageName, $attributes['packages']);

    } else {
      $execute = TRUE;
    }

    if ($execute) {
      $holder = new PHI_ParameterHolder($attributes, TRUE);
      $className = $attributes['class'];

      $filter = new $className($filterId, $holder);
      $filter->doFilter($this);

    } else {
      $this->filterChain();
    }
  }
}
