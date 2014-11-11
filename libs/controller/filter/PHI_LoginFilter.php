<?php
/**
 * {@link PHI_AuthorityUser::isLogin() ユーザのログイン状態} をチェックし、ログインが必要なアクションに未ログイン状態でアクセスした場合は指定したアクションへ強制フォワードします。
 *
 * modules/{module_name}/config/filters.yml の設定例:
 * <code>
 * # フィルタクラス名。
 * class: PHI_LoginFilter
 *
 * # 未ログイン時に遷移するアクション名。
 * forward:
 * </code>
 *
 * ログインを必要とするアクションのビヘイビア設定例:
 * <code>
 * login: TRUE
 * </code>
 *
 * @package controller.filter
 */
class PHI_LoginFilter extends PHI_Filter
{
  /**
   * @throws PHI_SecurityException ログインエラー時にフォワードするアクションが未指定の場合に発生。
   * @see PHI_Filter::doFilter()
   */
//  public function doFilter(PHI_HttpRequest $request, PHI_HttpResponse $response, PHI_FilterChain $chain)
  public function doFilter(PHI_FilterChain $chain)
  {
    $request = $this->getController()->getRequest();

    $login = PHI_Config::getBehavior()->getBoolean('login');
    $result = FALSE;

    if ($login) {
      if ($request->getSession()->getUser()->isLogin()) {
        $result = TRUE;
      }

    } else {
      $result = TRUE;
    }

    if ($result) {
      $chain->filterChain();

    } else {
      $forward = $this->_holder->get('forward');

      if ($forward !== NULL) {
        $this->getController()->forward($forward);

      } else {
        $message = 'Forward destination of non-login is not specified.';
        throw new PHI_SecurityException($message);
      }
    }
  }
}
