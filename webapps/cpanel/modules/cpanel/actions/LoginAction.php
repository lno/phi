<?php
/**
 * @package actions
 */
class LoginAction extends PHI_Action
{
  public function execute()
  {
    $loginPassword = $this->getForm()->get('loginPassword');
    $validPassword = PHI_Config::getApplication()->get('cpanel.password');

    if (strcmp($loginPassword, $validPassword) == 0) {
      $this->getUser()->addRole('cpanel');

      return PHI_View::SUCCESS;
    }

    $this->getMessages()->addError('ログイン認証に失敗しました。');
    $this->getForm()->clear();

    return PHI_View::ERROR;
  }
}
