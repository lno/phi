<?php
/**
 * テンプレート上で {@link PHI_AuthorityUser ユーザ情報} を取得するためのヘルパメソッドを提供します。
 * このヘルパは、$user という変数名であらかじめテンプレートにインスタンスが割り当てられています。
 *
 * <code>
 * <?php echo $user->{method}; ?>
 * </code>
 *
 * global_helpers.yml の設定例:
 * <code>
 * user:
 *   # ヘルパクラス名。
 *   class: PHI_UserHelper
 * </code>
 * <i>その他に指定可能な属性は {@link PHI_Helper} クラスを参照。</i>
 *
 * @package view.helper
 */
class PHI_UserHelper extends PHI_Helper
{
  /**
   * @var PHI_AuthorityUser
   */
  private $_user;

  /**
   * @see PHI_Helper::__construct()
   */
  public function __construct(PHI_View $currentView, array $config = array())
  {
    parent::__construct($currentView, $config);

    $this->_user = $this->getUser();
  }

  /**
   * {@link PHI_AuthorityUser::hasAttribute()} メソッドのエイリアスです。
   */
  public function hasAttribute($name)
  {
    return $this->_user->hasAttribute($name);
  }

  /**
   * {@link PHI_AuthorityUser::getAttribute()} メソッドに {@link PHI_StringUtils::escape() HTML エスケープ} 機能を追加した拡張メソッドです。
   *
   * @param string $name {@link PHI_AuthorityUser::getAttribute()} メソッドを参照。
   * @param bool $escape 値を HTML エスケープした状態で返す場合は TRUE を指定。
   * @return mixed name に対応する属性値を返します。
   */
  public function get($name, $escape = TRUE)
  {
    $value = $this->_user->getAttribute($name);

    if ($escape) {
      $value = PHI_StringUtils::escape($value);
    }

    return $value;
  }

  /**
   * {@link PHI_AuthorityUser::isLogin()} メソッドのエイリアスです。
   */
  public function isLogin()
  {
    return $this->_user->isLogin();
  }

  /**
   * {@link PHI_AuthorityUser::hasRole()} メソッドのエイリアスです。
   */
  public function hasRole($roles = NULL)
  {
    return $this->_user->hasRole($roles);
  }

  /**
   * トランザクショントークン ID を取得します。
   * <i>通常はテンプレート上でこのメソッドをコールする必要はありません。
   * トークン ID は {@link PHI_FormHelper::close()} メソッドをコールした時点で自動的にテンプレートの hidden フィールドに埋め込まれます。</i>
   *
   * @return string トランザクショントークン ID を返します。
   */
  public function getTokenId()
  {
    return $this->_user->getAttribute('tokenId');
  }
}
