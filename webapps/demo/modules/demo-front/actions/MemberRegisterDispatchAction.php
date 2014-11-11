<?php
/**
 * @package modules.entry.actions
 */
class MemberRegisterDispatchAction extends PHI_DispatchAction
{
  public function defaultForward()
  {
    return $this->dispatchMemberRegisterForm();
  }

  public function dispatchMemberRegisterForm()
  {
    return 'MemberRegisterForm';
  }

  public function dispatchMemberRegister()
  {
    return 'MemberRegister';
  }
}
