<?php
/**
 * @package actions
 */
class ConnectTestAction extends PHI_Action
{
  public function execute()
  {
    $this->getResponse()->write('SUCCESS');

    return PHI_View::NONE;
  }
}
