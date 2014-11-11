<?php
/**
 * @package actions
 */
class CacheClearAction extends PHI_Action
{
  public function execute()
  {
    $clearDirectory = $this->getRequest()->getAttribute('clearDirectory');
    PHI_FileUtils::deleteDirectory($clearDirectory, FALSE);

    $this->getMessages()->add('キャッシュを削除しました。');

    return PHI_View::SUCCESS;
  }
}
