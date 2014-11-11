<?php
/**
 * クライアントの情報やリクエストされたパラメータを取得するためのヘルパメソッドを提供します。
 * このヘルパは、$request という変数名であらかじめテンプレートにインスタンスが割り当てられています。
 *
 * <code>
 * <?php echo $request->{method}; ?>
 * </code>
 *
 * global_helpers.yml の設定例:
 * <code>
 * user:
 *   # ヘルパクラス名。
 *   class: PHI_RequestHelper
 * </code>
 * <i>その他に指定可能な属性は {@link PHI_Helper} クラスを参照。</i>
 *
 * @package view.helper
 */
class PHI_RequestHelper extends PHI_Helper
{
  /**
   * @var PHI_HttpRequest
   */
  private $_request;

  /**
   * @see PHI_Helper::__construct()
   */
  public function __construct(PHI_View $currentView, array $config = array())
  {
    parent::__construct($currentView, $config);

    $this->_request = $this->getRequest();
  }

  /**
   * {@link PHI_HttpRequest::hasParameter()} のエイリアスメソッドです。
   */
  public function hasName($name)
  {
    return $this->_request->hasParameter($name);
  }

  /**
   * {@link PHI_HttpRequest::getParameter()} メソッドに {@link PHI_StringUtils::escape() HTML エスケープ} の機能を追加した拡張メソッドです。
   *
   * @param string $name {@link PHI_HttpRequest::getParameter()} メソッドを参照。
   * @param bool $escape 値を HTML エスケープした状態で返す場合は TRUE を指定。
   * @return string name に対応するパラメータ値を返します。
   */
  public function get($name, $escape = TRUE)
  {
    $value = NULL;

    if ($this->_request->hasParameter($name)) {
      $value = $this->_request->getParameter($name);

      if ($escape) {
        $value = PHI_StringUtils::escape($value);
      }
    }

    return $value;
  }

  /**
   * PHI_HttpRequest オブジェクトを取得します。
   *
   * @return PHI_HttpRequest HTTP リクエストオブジェクトを返します。
   */
  public function getContext()
  {
    return $this->_request;
  }
}
