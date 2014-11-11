<?php
/**
 * {@link PHI_AuthorityUser::getRoles() ユーザロール} がビヘイビアに定義されたロールを満たしていない (1 つ以上のロールにマッチしない) 場合に {@link PHI_SecurityException} をスローします。
 *
 * @package controller.filter
 */
class PHI_ACLFilter extends PHI_Filter
{
  /**
   * @throws PHI_SecurityException ユーザロールが不足している場合に発生。
   * @see PHI_Filter::doFilter()
   */
//  public function doFilter(PHI_HttpRequest $request, PHI_HttpResponse $response, PHI_FilterChain $chain)
  public function doFilter(PHI_FilterChain $chain)
  {
    // TODO TEST
//    $user = $request->getSession()->getUser();
    $user = $this->getController()->getRequest()->getSession()->getUser();

    if ($user->isCurrentActionAuthenticated(PHI_AuthorityUser::REQUIRED_ONE_ROLE)) {
      $chain->filterChain();

    } else {
      $roles = PHI_Config::getBehavior()->get('roles')->toArray();
      $message = sprintf('User roll is not enough. [%s]', implode(',', $roles));

      throw new PHI_SecurityException($message);
    }
  }
}
