<?php
/**
 * @package actions
 */
class LoginFormAction extends PHI_Action
{
  public function execute()
  {
    if ($this->getUser()->hasRole('cpanel')) {
      $this->getController()->forward('Home');

      return PHI_View::NONE;
    }

    return PHI_View::SUCCESS;
  }
}
