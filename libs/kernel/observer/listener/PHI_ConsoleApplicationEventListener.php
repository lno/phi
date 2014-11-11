<?php
/**
 * コンソールアプリケーションのためのイベントリスナです。
 *
 * @package kernel.observer.listener
 */
class PHI_ConsoleApplicationEventListener extends PHI_ApplicationEventListener
{
  /**
   * @see PHI_ApplicationEventListener::getListenEvents()
   */
  public function getListenEvents()
  {
    return array();
  }

  /**
   * @see PHI_ApplicationEventListener::getBootMode()
   */
  public function getBootMode()
  {
    return PHI_BootLoader::BOOT_MODE_CONSOLE;
  }
}
