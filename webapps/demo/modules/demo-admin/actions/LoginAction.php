<?php
/**
 * @package modules.manager.actions
 */
class LoginAction extends PHI_Action
{
  public function execute()
  {
    $form = $this->getForm();

    $loginId = $form->get('loginId');
    $loginPassword = sha1('salt' . $form->get('loginPassword'));

    $managersDAO = PHI_DAOFactory::create('Managers');
    $manager = $managersDAO->find($loginId, $loginPassword);

    if ($manager) {
      $user = $this->getUser();

      $user->addRole('manager');
      $user->setAttribute('manager', $manager);

      return PHI_View::SUCCESS;
    }

    $this->getMessages()->addError('ログイン認証に失敗しました。');

    return PHI_View::ERROR;
  }
}
