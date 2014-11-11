<?php
/**
 * @package modules.entry.actions
 */
class ShowPreviewImageAction extends PHI_Action
{
  public function validateErrorHandler()
  {
    return PHI_View::NONE;
  }

  public function execute()
  {
    $tokenId = $this->getUser()->getAttribute('tokenId');
    $previewPath = $this->getService('Member')->getIconPreviewPath($tokenId);

    if (is_file($previewPath)) {
      $data = PHI_FileUtils::readFile($previewPath);
      $this->getResponse()->writeBinary($data, 'image/jpeg');
    }

    return PHI_View::NONE;
  }
}
