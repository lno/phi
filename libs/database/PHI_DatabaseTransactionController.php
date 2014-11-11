<?php
/**
 * データベーストランザクションを管理します。
 *
 * @package database
 */
class PHI_DatabaseTransactionController extends PHI_Object
{
  /**
   * @var PHI_DatabaseConnection
   */
  private $_connection;

  /**
   * @var bool
   */
  private $_isActiveTransaction = FALSE;

  /**
   * トランザクションコントローラに実行クエリを通知します。
   *
   * @param PHI_DatabaseConnection $connection コネクションオブジェクト。
   * @param string $query 実行クエリ。
   */
  public function notify($connection, $query)
  {
    $compare = substr(ltrim($query), 0, 6);

    if (strcasecmp($compare, 'INSERT') == 0 || strcasecmp($compare, 'UPDATE') == 0 || strcasecmp($compare, 'DELETE') == 0) {
      if (!$this->_isActiveTransaction) {
        $connection->beginTransaction();

        $this->_connection = $connection;
        $this->_isActiveTransaction = TRUE;
      }
    }
  }

  /**
   * @see PHI_DatabaseConnection::isActiveTransaction()
   */
  public function isActiveTransaction()
  {
    return $this->_isActiveTransaction;
  }

  /**
   * @see PHI_DatabaseConnection::rollback()
   */
  public function rollback()
  {
    return $this->_connection->rollback();
  }

  /**
   * @see PHI_DatabaseConnection::commit()
   */
  public function commit()
  {
    return $this->_connection->commit();
  }
}
