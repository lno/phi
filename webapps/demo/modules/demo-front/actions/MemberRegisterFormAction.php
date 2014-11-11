<?php
/**
 * @package modules.entry.actions
 */
class MemberRegisterFormAction extends PHI_Action
{
  public function execute()
  {
    // トランザクショントークンを発行
    $this->getUser()->saveToken();

    // 誕生年の初期値
    if (!$this->getMessages()->hasFieldError()) {
      $this->getForm()->set('birth.year', 1980);
    }

    return PHI_View::SUCCESS;
  }
}
