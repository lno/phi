<?php
/**
 * {@link PHI_DatabaseTransactionController データベーストランザクション} を制御するリスナです。
 * リスナをアプリケーションに適用することで、アプリケーションロジック側でのコミット制御 (BEGIN、COMMIT、ROLLBACK) が不要となり、プログラムが正常終了する時点で自動コミットが行われるようになります。
 * 尚、プログラム内で例外やエラーが発生 (またはプログラム内で exit を実行) した場合は、全てのトランザクションがロールバックされます。
 *
 * application.yml の設定例:
 * <code>
 * observer:
 *   listeners:
 *     databaseTransactionListener:
 *       class: PHI_DatabaseTransactionListener
 * </code>
 *
 * @package kernel.observer.listener
 */
class PHI_DatabaseTransactionListener extends PHI_ApplicationEventListener
{
  /**
   * @var PHI_DatabaseManager
   */
  private $_database;

  /**
   * @see PHI_ApplicationEventListener::getListenEvents()
   */
  public function getListenEvents()
  {
    return array('preProcess', 'postProcess');
  }

  /**
   * @see PHI_ApplicationEventListener::getBootMode()
   */
  public function getBootMode()
  {
    return PHI_BootLoader::BOOT_MODE_WEB|PHI_BootLoader::BOOT_MODE_CONSOLE;
  }

  /**
   * @see PHI_ApplicationEventListener::preProcess()
   */
  public function preProcess()
  {
    $database = PHI_DatabaseManager::getInstance();
    $database->setTransactionController(new PHI_DatabaseTransactionController());

    $this->_database = $database;
  }

  /**
   * @see PHI_ApplicationEventListener::postProcess()
   */
  public function postProcess()
  {
    $connections = $this->_database->getConnections();

    foreach ($connections as $connection) {
      $controller = $connection->getTransactionController();

      if ($controller->isActiveTransaction()) {
        $controller->commit();
      }
    }
  }
}
