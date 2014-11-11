<?php
/**
 * @package libs.filter
 */
class RoleAuthenticationFilter extends PHI_Filter
{
  public function doFilter(PHI_FilterChain $chain)
  {
    // 認証済みであればアクションを実行
    if ($this->getUser()->isCurrentActionAuthenticated()) {
      $chain->filterChain();

    } else {
      $this->getMessages()->addError('ログインを行って下さい。');
      $this->getController()->forward('LoginForm');
    }
  }
}
