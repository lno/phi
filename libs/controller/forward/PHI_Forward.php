<?php
/**
 * アクションのフォワード情報を管理します。
 *
 * @package controller.forward
 */
class PHI_Forward extends PHI_Object
{
  /**
   * @var string
   */
  private $_moduleName;

  /**
   * @var string
   */
  private $_actionName;

  /**
   * @var PHI_Action
   */
  private $_action;

  /**
   * コンストラクタ。
   *
   * @param string $moduleName モジュール名。
   * @param string $actionName アクション名。
   */
  public function __construct($moduleName, $actionName)
  {
    $this->_moduleName = $moduleName;
    $this->_actionName = $actionName;
  }

  /**
   * モジュール名を取得します。
   *
   * @return string モジュール名を返します。
   */
  public function getModuleName()
  {
    return $this->_moduleName;
  }

  /**
   * アクション名を取得します。
   *
   * @return string アクション名を返します。
   */
  public function getActionName()
  {
    return $this->_actionName;
  }

  /**
   * アクションオブジェクトを設定します。
   *
   * @param PHI_Action $action アクションオブジェクトを設定します。
   */
  public function setAction(PHI_Action $action)
  {
    $this->_action = $action;
  }

  /**
   * アクションオブジェクトを取得します。
   *
   * @return PHI_Action アクションオブジェクトを取得します。
   */
  public function getAction()
  {
    return $this->_action;
  }
}

