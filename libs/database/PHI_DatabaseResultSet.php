<?php
/**
 * データベースから取得したレコードの結果セットを保持します。
 * レコードの結果セットは、{@link PHI_DatabaseStatement::executeQuery()} メソッドで取得することができます。
 * PHI_DatabaseResultSet は初期状態でカーソルが先頭行に配置されるため、read() 等のメソッドでカーソルを移動できるほか、Iterator インタフェースを実装しているため、foreach() でオブジェクトを取得することもできます。
 *
 * <code>
 * $conn = $this->getDatabase()->getConnection();
 * $stmt = $conn->createStatement('SELECT * FROM members');
 * $resultSet = $stmt->executeQuery();
 *
 * // カーソルを次の行に移す
 * while (($record = $resultSet->read()) !== FALSE) {
 *   // ...
 * }
 *
 * // foreach() を使ったループ処理
 * foreach ($resultSet as $record) {
 *   // ...
 * }
 * </code>
 *
 * @package database
 */
class PHI_DatabaseResultSet extends PHI_Object implements Iterator
{
  /**
   * ステートメントオブジェクト。
   * @var PHI_DatabaseStatement
   */
  private $_statement;

  /**
   * フェッチタイプ。
   * @var int
   */
  private $_fetchType;

  /**
   * 先頭行のレコードオブジェクト。
   * @var PHI_RecordObject
   */
  private $_firstRecordObject;

  /**
   * カーソルポインタ。
   * @var int
   */
  private $_position = 0;

  /**
   * 現在行のレコードオブジェクト。
   * @var PHI_RecordObject
   */
  private $_current = NULL;

  /**
   * 結果セットの有効状態。
   * @var bool
   */
  private $_isActive = TRUE;

  /**
   * コンストラクタ。
   *
   * @param PDOStatement $statement ステートメントオブジェクト。
   * @param int $fetchType フェッチタイプ。PDO::FETCH_* 定数を指定。
   */
  public function __construct(PDOStatement $statement, $fetchType)
  {
    $this->_fetchType = $fetchType;
    $this->_statement = $statement;
  }

  /**
   * @see Iterator::current()
   */
  public function current()
  {
    return $this->_current;
  }

  /**
   * @see Iterator::key()
   */
  public function key()
  {
    return $this->_position;
  }

  /**
   * @see Iterator::next()
   */
  public function next()
  {
    $this->_current = $this->read($this->_fetchType);
    $this->_position++;
  }

  /**
   * @see Iterator::rewind()
   */
  public function rewind()
  {
    $this->_position = 0;
    $this->_current = $this->read($this->_fetchType);
  }

  /**
   * @see Iterator::valid()
   */
  public function valid()
  {
    $result = FALSE;

    if ($this->_current !== FALSE) {
      $result = TRUE;
    }

    return $result;
  }

  /**
   * フィールドを PHP 変数にバインドします。
   * このメソッドの実装は、{@link PDOStatement::bindColumn()} に依存しています。
   *
   * @param string $field 結果セットに含まれる (1 から始まる) フィールドインデックス。またはフィールド名。
   * @param string $name フィールドをバインドする PHP の変数名。
   * @param int $dataType データタイプ。PDO::PARAM_* 定数を指定可能。
   * @param int $length データ長。領域を事前に確保するためのヒント。
   * @return bool バインドに成功した場合は TRUE、失敗した場合は FALSE を返します。
   */
  public function bindField($field, &$name, $dataType = PDO::PARAM_STR, $length = NULL)
  {
    $this->_statement->bindColumn($field, $name, $dataType, $length);
  }

  /**
   * 結果セットに含まれるフィールド数を取得します。
   *
   * @return int 結果セットに含まれるフィールド数を返します。
   */
  public function getFieldCount()
  {
    return $this->_statement->columnCount();
  }

  /**
   * 結果セットの全レコード数を取得します。
   *
   * @return int 結果セットの全レコード数を返します。
   */
  public function getRowCount()
  {
    return $this->_statement->rowCount();
  }

  /**
   * 結果セットから次の行を取得します。
   *
   * @return mixed 戻り値はフェッチタイプにより異なります。
   *   詳しくは {@link PHI_DatabaseStatement::setFetchMode()} メソッドを参照して下さい。
   *   データが存在しない場合は FALSE を返します。
   */
  public function read()
  {
    $record = $this->_statement->fetch($this->_fetchType);

    if ($record === FALSE) {
      $this->_statement->closeCursor();

    } else if ($this->_firstRecordObject === NULL) {
      $this->_firstRecordObject = $record;
    }

    return $record;
  }

  /**
   * 結果セットに残っている全てのレコードを取得します。
   * <strong>このメソッドは発行するクエリによって、大量のシステム・ネットワークリソースを消費する恐れがあります。
   * {@link read()} メソッドへの置き換えや、SQL の WHERE、LIMIT 句などで結果を制限することを推奨します。</strong>
   *
   * @return array 結果セットに含まれる残りのレコードを返します。
   *   レコードのデータ型はフェッチタイプにより異なります。
   *   詳しくは {@link PHI_DatabaseStatement::setFetchMode()} メソッドを参照して下さい。
   *   データが存在しない場合は空の配列を返します。
   */
  public function readAll()
  {
    $records = $this->_statement->fetchAll();

    if ($this->_firstRecordObject === NULL && sizeof($records)) {
      $this->_firstRecordObject = $records[0];
    }

    return $records;
  }

  /**
   * 結果セットに含まれる最初のレコードを取得します。
   * このメソッドはカーソルが先頭行にある場合、カーソル位置を次の行に移します。
   *
   * @return PHI_RecordObject 最初のレコードを返します。
   */
  public function readFirst()
  {
    $record = FALSE;

    if ($this->_firstRecordObject !== NULL) {
      $record = $this->_firstRecordObject;
    } else {
      $record = $this->read();
    }

    return $record;
  }

  /**
   * 現在のレコードから任意フィールドの値を取得します。
   *
   * @param mixed $record レコードデータ。データの型はフェッチタイプにより異なります。
   * @param mixed $key 取得対象のフィールドインデックス (0 から開始)、またはフィールド名。
   * @return string フィールドの値を返します。フィールドが存在しない場合は FALSE を返します。
   */
  private function findRecordValue($record, $key)
  {
    $result = FALSE;

    // フィールド名検索
    if (is_string($key)) {
      if (isset($record[$key])) {
        $result = $record[$key];
      }

    // フィールドインデックス検索
    } else {
      // レコードがオブジェクト (PHI_RecordObject) の場合
      if (is_object($record)) {
        $fieldNames = $record->getNames();

      // レコードが配列の場合
      } else {
        $fieldNames = array_keys($record);
      }

      if (isset($fieldNames[$key])) {
        $result = $record[$fieldNames[$key]];
      }
    }

    return $result;
  }

  /**
   * 結果セットから次の行の単一フィールドを取得します。
   *
   * @param mixed $key 取得対象のフィールドインデックス (0 から開始)、またはフィールド名。
   * @return string フィールドの値を返します。フィールドが存在しない場合は FALSE を返します。
   */
  public function readField($key = 0)
  {
    $result = FALSE;

    if ($record = $this->read()) {
      $result = $this->findRecordValue($record, $key);
    }

    return $result;
  }

  /**
   * 結果セットに含まれる指定フィールドの値を配列形式で取得します。
   *
   * @param mixed $key 取得対象のフィールドインデックス (0 から開始)、またはフィールド名。
   * @return array key フィールドに対応する値を配列形式で返します。
   */
  public function readAllByIndex($key = 0)
  {
    $records = array();

    while ($record = $this->read()) {
      $records[] = $this->findRecordValue($record, $key);
    }

    return $records;
  }

  /**
   * 結果セットから key と value で構成される連想配列データを取得します。
   *
   * @param mixed $key 連想配列のキーとするフィールド名。
   *   未指定の場合は 1 番目のフィールドをキーとして取得。
   * @param mixed $value 連想配列の値とするフィールド名。
   *   value が指定されてる場合は同じキー名が値となり、未指定の場合は 2 番目のフィールドを値として取得する。
   * @return array key と value で構成される連想配列データを返します。
   */
  public function readAllByHash($key = NULL, $value = NULL)
  {
    $hash = array();

    while ($record = $this->read()) {
      if ($key === NULL) {
        $key = 0;

        if ($value === NULL) {
          $value = 1;
        }
      }

      if ($value === NULL) {
        $value = $key;
      }

      $hashKey = $this->findRecordValue($record, $key);
      $hashValue = $this->findRecordValue($record, $value);

      $hash[$hashKey] = $hashValue;
    }

    return $hash;
  }

  /**
   * 結果セットが有効な状態にあるかどうかチェックします。
   *
   * @return bool 結果セットが有効であれば TRUE、無効 (カーソルが最終行に達した場合) であれば FALSE を返します。
   */
  public function isActive()
  {
    return $this->_isActive;
  }

  /**
   * オブジェクトが複数の結果セットを持つ場合に、次の結果セットに移動します。
   *
   * @return bool 結果セットの移動に成功した場合は TRUE、失敗した場合は FALSE を返します。
   */
  public function getNextResultSet()
  {
    return $this->_statement->nextRowset();
  }

  /**
   * 結果セットを閉じます。
   */
  public function close()
  {
    $this->_statement->closeCursor();

    $this->_firstRecordObect = NULL;
    $this->_current = NULL;
    $this->_isActive = FALSE;
  }
}
