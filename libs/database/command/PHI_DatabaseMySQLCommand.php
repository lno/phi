<?php
/**
 * このクラスは、実験的なステータスにあります。
 * これは、この関数の動作、関数名、ここで書かれていること全てが phi の将来のバージョンで予告なく変更される可能性があることを意味します。
 * 注意を喚起するとともに自分のリスクでこのクラスを使用してください。
 *
 * @package database.command
 */
class PHI_DatabaseMySQLCommand extends PHI_DatabaseCommand
{
  /**
   * @see PHI_DatabaseCommand::getTables()
   */
  public function getTables()
  {
    $query = 'SHOW FULL TABLES WHERE TABLE_TYPE = :table_type';

    $stmt = $this->_connection->createStatement($query);
    $stmt->bindValue(':table_type', 'BASE TABLE');
    $resultSet = $stmt->executeQuery();
    $tables = $resultSet->readAllByIndex(0);

    return $tables;
  }

  /**
   * @see PHI_DatabaseCommand::getTableSize()
   */
  public function getTableSize($tableName)
  {
    $result = FALSE;

    if ($this->existsTable($tableName)) {
      $query = 'SHOW TABLE STATUS LIKE :table_name';

      $stmt = $this->_connection->createStatement($query);
      $stmt->bindValue(':table_name', $tableName);
      $resultSet = $stmt->executeQuery();
      $result = $resultSet->read()->Data_length;
    }

    return $result;
  }

  /**
   * @see PHI_DatabaseCommand::getTables()
   */
  public function getViews()
  {
    $query = 'SHOW FULL TABLES WHERE TABLE_TYPE = :table_type';

    $stmt = $this->_connection->createStatement($query);
    $stmt->bindValue(':table_type', 'VIEW');
    $resultSet = $stmt->executeQuery();
    $tables = $resultSet->readAllByIndex(0);

    return $tables;
  }

  /**
   * @see PHI_DatabaseCommand::getFields()
   */
  public function getFields($tableName)
  {
    $query = 'SHOW COLUMNS FROM ' . $tableName;
    $resultSet = $this->_connection->rawQuery($query);

    $fields = array();

    while ($record = $resultSet->read()) {
      $fields[] = $record->getByIndex(0);
    }

    return $fields;
  }

  /**
   * @param string $type
   * @param null $length
   * @return null|string
   */
  public function getNativeDataType($type, $length = NULL)
  {
    $nativeType = NULL;

    switch ($type) {
      case 'timestamp':
        $nativeType = 'datetime';
        break;

      default:
        $nativeType = parent::getNativeDataType($type, $length);
        break;
    }

    if ($length) {
      $nativeType = sprintf('%s(%s)', $type, $length);
    }

    return $nativeType;
  }

  /**
   * マスタのバイナリログに関するステータス情報を取得します。
   *
   * @return array ステータス情報を配列形式で返します。値が取得できない場合は FALSE を返します。
   */
  public function getMasterStatus()
  {
    $stmt = $this->_connection->createStatement('SHOW MASTER STATUS');
    $stmt->setFetchMode(PHI_DatabaseStatement::FETCH_TYPE_CLASS);
    $data = $stmt->executeQuery()->read()->toArray();

    return $data;
  }

  /**
   * スレーブのバイナリログに関するステータス情報を取得します。
   *
   * @return array ステータス情報を配列形式で返します。値が取得できない場合は FALSE を返します。
   */
  public function getSlaveStatus()
  {
    $result = FALSE;

    $stmt = $this->_connection->createStatement('SHOW SLAVE STATUS');
    $stmt->setFetchMode(PHI_DatabaseStatement::FETCH_TYPE_CLASS);
    $resultSet = $stmt->executeQuery();

    if ($record = $resultSet->read()) {
      $result = $record->toArray();
    }

    return $result;
  }

  /**
   * @see PHI_DatabaseCommand::getVersion()
   */
  public function getVersion($numberOnly = FALSE)
  {
    $resultSet = $this->_connection->rawQuery('SELECT version()');
    $version = $resultSet->readField(0);

    if ($numberOnly) {
      $version = substr($version, 0, strpos($version, '-'));
    }

    return $version;
  }
}
