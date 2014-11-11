<?php
/**
 * @package actions
 */
class LogoutAction extends PHI_Action
{
  public function execute()
  {
    $this->getUser()->clear();

    return PHI_View::SUCCESS;
  }
}
