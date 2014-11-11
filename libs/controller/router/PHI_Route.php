<?php
/**
 * ルート情報を管理します。
 *
 * @package controller.router
 */
class PHI_Route extends PHI_Object
{
  /**
   * @var array
   */
  private $_pathHolder = array();

  /**
   * @var PHI_ForwardStack
   */
  private $_forwardStack;

  /**
   * コンストラクタ。
   *
   * @param array $pathHolder ルートを構築するパスホルダ情報。
   *   - route: ルート名
   *   - module: モジュール名
   *   - action: アクション名
   *   その他、リクエスト URI に含むパスホルダパラメータも格納する。
   */
  public function __construct($pathHolder = array())
  {
    $this->_pathHolder = $pathHolder;
    $this->_forwardStack = new PHI_ForwardStack();
  }

  /**
   * ルート名を取得します。
   *
   * @return string ルート名を返します。
   */
  public function getRouteName()
  {
    return $this->_pathHolder['route'];
  }

  /**
   * モジュール名を取得します。
   *
   * @return string ルート名を返します。
   */
  public function getModuleName()
  {
    return $this->_pathHolder['module'];
  }

  /**
   * アクション名を取得します。
   *
   * @return string アクション名を返します。
   */
  public function getActionName()
  {
    return $this->_pathHolder['action'];
  }

  /**
   * パスホルダに含まれる全てのパラメータを取得します。
   *
   * @return array パスホルダに含まれる全てのパラメータを返します。
   */
  public function getPathHolder()
  {
    return $this->_pathHolder;
  }

  /**
   * フォワードスタックオブジェクトを取得します。
   *
   * @return PHI_ForwardStack フォワードスタックオブジェクトを返します。
   */
  public function getForwardStack()
  {
    return $this->_forwardStack;
  }
}
