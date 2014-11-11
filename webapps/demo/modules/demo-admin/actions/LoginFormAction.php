<?php
/**
 * @package modules.manager.actions
 */
class LoginFormAction extends PHI_Action
{
  public function execute()
  {
    // 既に認証済みであれば Home アクションにフォワード
    if ($this->getUser()->hasRole('manager')) {
      $this->getController()->forward('Home');

      return PHI_View::NONE;
    }

    return PHI_View::SUCCESS;
  }
}
