<?php
/**
 * HTTP 認証を行うための抽象クラスです。
 * HTTP 認証は Apache モジュールとして実行した時のみ有効です。CGI 版では利用できません。
 *
 * @link http://www.ietf.org/rfc/rfc2617.txt HTTP Authentication: Basic and Digest Access Authentication
 * @package controller.filter
 */
abstract class PHI_HttpAuthenticationFilter extends PHI_Filter
{
  /**
   * レルムを取得します。
   *
   * @return string レルムを返します。デフォルトではモジュール名を返します。
   */
  public function getRealm()
  {
    return PHI_FrontController::getInstance()->getRequest()->getRoute()->getModuleName();
  }

  /**
   * ログインプロンプトを表示します。
   *
   * @param PHI_FilterChain $chain フィルタチェインのインスタンス。
   */
  abstract public function showLoginPrompt(PHI_FilterChain $chain);

  /**
   * 認証成功時に実行する処理を実装します。
   * メソッドがオーバーライドされていない場合は、次のフィルタが実行されます。
   *
   * @param PHI_FilterChain $chain フィルタチェインのインスタンス。
   */
  public function authenticateSuccess(PHI_FilterChain $chain)
  {
    $chain->filterChain();
  }

  /**
   * 認証失敗時に実行する処理を実装します。
   * メソッドがオーバーライドされていない場合は、{@link showLoginPrompt()} メソッドが実行されます。
   *
   * @param PHI_FilterChain $chain フィルタチェインのインスタンス。
   */
  public function authenticateFailure(PHI_FilterChain $chain)
  {
    $this->showLoginPrompt($chain);
  }

  /**
   * 認証失敗時の処理を実装します。
   * メソッドがオーバーライドされていない場合、HTTP ステータスコード 401 (Unauthorized) を出力して処理を停止します。
   *
   * @param PHI_FilterChain $chain フィルタチェインのインスタンス。
   */
  public function authenticateCancel(PHI_FilterChain $chain)
  {
    $this->getResponse()->sendError(401);
    die();
  }
}
