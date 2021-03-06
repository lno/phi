<?php
/**
 * クライテリアはレコードの抽出条件をスコープとして管理し、SQL を書くことなくデータを取得するメソッドを提供します。
 * <code>
 * class UsersDAO extends PHI_DAO
 * {
 *   // DAO クラスにクライテリアで利用する抽出条件 (スコープ) を宣言
 *   public function scopes(PHI_DatabaseCriteriaScopes $scopes) {
 *     // 抽出条件を配列形式で指定
 *     $scopes->add('condition1',
 *        array(
 *          // 値には SQL のコードを書くことが可能
 *          // キーに指定可能な値は {@link PHI_DatabaseCriteriaScopes::add()} メソッドを参照
 *          'where' => 'track_id = 200'
 *        )
 *     );
 *
 *     // 抽出条件をクロージャ形式で指定 (条件文に任意の値を指定可能)
 *     $scopes->add('condition2',
 *       function($registerDate) {
 *         return array(
 *          'where' => "register_date = $registerDate",
 *          'order' => 'user_id DESC'
 *         );
 *       )
 *     }
 *   }
 * }
 * </code>
 *
 * <i>現在のところ、クライテリアはリレーションには対応していません。
 *
 * @package database.criteria
 */
class PHI_DatabaseCriteria extends PHI_Object
{
  /**
   * @var string
   */
  private $_dataSourceId;

  /**
   * @var string
   */
  private $_tableName;

  /**
   * @var array
   */
  private $_primaryKeys;

  /**
   * @var bool
   */
  private $_primaryKeyConstraint = FALSE;

  /**
   * @var mixed
   */
  private $_primaryKeyValue;

  /**
   * @var array
   */
  private $_parimaryValues = array();

  /**
   * @var array
   */
  private $_scopes;

  /**
   * @var array
   */
  private $_callbacks = array();

  /**
   * @var array
   */
  private $_conditions = array(
    'select' => '*',
    'from' => NULL,
    'where' => array(),
    'group' => NULL,
    'having' => NULL,
    'order' => NULL,
    'limit' => NULL,
    'offset' => NULL,
    'options' => NULL
  );

  /**
   * @var array
   */
  private $_callbackes = array();

  /**
   * コンストラクタ。
   *
   * @param string $dataSourceId データソース ID。
   * @param string $tableName テーブル名。
   * @param PHI_DatabaseCriteriaScopes $scopes スコープオブジェクト。
   */
  public function __construct($dataSourceId,
    $tableName,
    array $primaryKeys = array(),
    PHI_DatabaseCriteriaScopes $scopes = NULL)
  {
    $this->_dataSourceId = $dataSourceId;
    $this->_tableName = $tableName;
    $this->_primaryKeys = $primaryKeys;

    if ($scopes !== NULL) {
      $this->_scopes = $scopes->getScopes();
    }
  }

  /**
   * @return PHI_DatabaseConnection
   */
  private function getConnection()
  {
    $database = PHI_DatabaseManager::getInstance();

    if (isset($this->_conditions['options']['dataSourceId'])) {
      $dataSourceId = $this->_conditions['options']['dataSourceId'];
    } else {
      $dataSourceId = $this->_dataSourceId;
    }

    $connection = $database->getConnection($dataSourceId);

    return $connection;
  }

  /**
   * クライテリアにプライマリキーのレコード抽出条件制約を設定します。
   * <code>
   * $criteria = PHI_DAOFactory::create('Users')->createCriteria();
   *
   * // プライマリキーが持つ値
   * $criteria->setPrimaryKeyValue(100);
   *
   * // 'SELECT * FROM user_id WHERE user_id = 100'
   * $criteria->getQuery();
   * </code>
   *
   * @param mixed $primaryKeyValue {@link PHI_DAO::getPrimaryKyes() プライマリキー} が持つ値。
   *   プライマリキーが複数フィールドで構成される場合は配列形式で値を指定。
   * @return PHI_DatabaseCriteria クライテリアオブジェクトを返します。
   */
  public function setPrimaryKeyValue($primaryKeyValue)
  {
    $this->_primaryKeyConstraint = TRUE;
    $this->_primaryKeyValue = $primaryKeyValue;

    return $this;
  }

  /**
   * レコードの取得範囲を設定します。
   *
   * @param int $limit レコードの取得数。
   * @param int $offset レコードの取得開始位置。
   * @return PHI_DatabaseCriteria クライテリアオブジェクトを返します。
   */
  public function setRange($limit, $offset = 0)
  {
    $this->_conditions['limit'] = $limit;
    $this->_conditions['offset'] = $offset;

    return $this;
  }

  /**
   * 参照クエリを構築します。
   *
   * @param array $conditions 抽出条件を含む配列。
   * @return string 構築した参照クエリを返します。
   * @throws RuntimeException プライマリキーが未定義の場合に発生。
   */
  private function buildSelectQuery(array $conditions)
  {
    // 'SELECT' 句の生成
    $query = 'SELECT ' . $conditions['select'];

    // 'FROM' 句の生成
    if ($conditions['from'] !== NULL) {
      $query .= ' FROM ' . $conditions['from'];
    } else {
      $query .= ' FROM ' . $this->_tableName;
    }

    // 'WHERE' 句の生成
    if ($this->_primaryKeyConstraint) {
      $wherePrimaryQuery = NULL;

      $valueSize = sizeof($this->_primaryKeyValue);
      $primaryKeySize = sizeof($this->_primaryKeys);
      $hasError = FALSE;

      if ($primaryKeySize == 0) {
        $message = sprintf('Primary key is undefined. [%s::$_primaryKeys]', get_class($this));
        throw new RuntimeException($message);
      }

      $connection = $this->getConnection();

      if ($valueSize > 1) {
        if ($primaryKeySize != $valueSize) {
          $hasError = TRUE;

        } else {
          for ($i = 0; $i < $primaryKeySize; $i++) {
            if ($i > 0) {
              $wherePrimaryQuery .= ' AND ';
            }

            $wherePrimaryQuery .= sprintf('%s = %s',
              $this->_primaryKeys[$i],
              $connection->quote($this->_primaryKeyValue[$i]));
          }
        }

      } else {
        if ($primaryKeySize > 1) {
          $hasError = TRUE;

        } else {
          if (is_array($this->_primaryKeyValue)) {
            $primaryValue = $this->_primaryKeyValue[0];
          } else {
            $primaryValue = $this->_primaryKeyValue;
          }

          $wherePrimaryQuery = sprintf('%s = %s',
            $this->_primaryKeys[0],
            $connection->quote($primaryValue));
        }
      }

      if ($hasError) {
        $message = 'Does not match the number of primary key and values.';
        throw new InvalidArgumentException($message);
      }

      $conditions['where'][] = array($wherePrimaryQuery, 'AND');
    }

    $j = sizeof($conditions['where']);

    if ($j > 0) {
      $query .= ' WHERE ';

      for ($i = 0; $i < $j; $i++) {
        if ($i > 0) {
          $query .= ' ' . $conditions['where'][$i][1] . ' ';
        }

        $query .= $conditions['where'][$i][0];
      }
    }

    // 'GROUP BY' 句の生成
    if ($conditions['group'] !== NULL) {
      $query .= ' GROUP BY ' . $conditions['group'];
    }

    // 'HAVING' 句の生成
    if ($conditions['having'] !== NULL) {
      $query .= ' HAVING ' . $conditions['having'];
    }

    // 'ORDER' 句の生成
    if ($conditions['order'] !== NULL) {
      $query .= ' ORDER BY ' . $conditions['order'];
    }

    // 'LIMIT' 句の生成
    if ($conditions['limit'] !== NULL) {
      $query .= ' LIMIT ' . $conditions['limit'];
    }

    // 'OFFSET' 句の生成
    if ($conditions['offset'] !== NULL) {
      $query .= ' OFFSET ' . $conditions['offset'];
    }

    return $query;
  }

  /**
   * クライテリアが生成したクエリを取得します。
   *
   * @return string クライテリアが生成したクエリを返します。
   */
  public function getQuery()
  {
    return $this->buildSelectQuery($this->_conditions);
  }

  /**
   * 条件に一致するレコードが存在するかどうかチェックします。
   *
   * @return bool レコードが存在する場合は TRUE、存在しない場合は FALSE を返します。
   */
  public function exists()
  {
    if ($this->find()) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * 条件に一致するレコードの件数を取得します。
   *
   * @return int レコードの件数を返します。
   */
  public function count()
  {
    $conditions = $this->_conditions;
    $conditions['select'] = 'COUNT(*)';

    $query = $this->buildSelectQuery($conditions);
    $rs = $this->getConnection()->rawQuery($query);

    return $rs->read()->getByIndex(0);
  }

  /**
   * 条件に一致するレコードを取得します。
   *
   * @return PHI_RecordObject 条件に一致するレコードオブジェクトを返します。
   */
  public function find()
  {
    $conditions = $this->_conditions;
    $conditions['limit'] = 1;
    $conditions['offset'] = 0;

    $query = $this->buildSelectQuery($conditions);
    $rs = $this->getConnection()->rawQuery($query);
    $record = $rs->read();

    // ディスクロージャの実行
    if (sizeof($this->_callbacks)) {
      foreach ($this->_callbacks as $callback) {
        $callback($record);
      }
    }

    return $record;
  }

  /**
   * プライマリキー制約を元に先頭行のレコードを取得します。
   *
   * $criteria = PHI_DAOFactory::create('Users')->createCriteria();
   *
   * // 'SELECT * FROM users ORDER BY user_id ASC LIMIT 1 OFFSET 0'
   * $criteria->findFirst()->getQuery();
   *
   * @return PHI_RecordObject 先頭行のレコードを返します。
   */
  public function findFirst()
  {
    return $this->getLimitRecords('ASC');
  }

  /**
   * プライマリキー制約を元に最終行のレコードを取得します。
   *
   * $criteria = PHI_DAOFactory::create('Users')->createCriteria();
   *
   * // 'SELECT * FROM users ORDER BY user_id DESC LIMIT 1 OFFSET 0'
   * $criteria->findLast()->getQuery();
   *
   * @return PHI_RecordObject 最終行のレコードを返します。
   */
  public function findLast()
  {
    return $this->getLimitRecords('DESC');
  }

  /**
   * @param string $type
   * @return PHI_RecordObject
   */
  private function getLimitRecords($type)
  {
    $conditions = $this->_conditions;
    $conditions['limit'] = 1;
    $conditions['offset'] = 0;

    $orderQuery = NULL;

    foreach ($this->_primaryKeys as $primaryKey) {
      $orderQuery .= sprintf('%s %s, ', $primaryKey, $type);
    }

    $conditions['order'] = rtrim($orderQuery, ', ');

    $query = $this->buildSelectQuery($conditions);
    $rs = $this->getConnection()->rawQuery($query);
    $record = $rs->read();

    // ディスクロージャの実行
    if (sizeof($this->_callbacks)) {
      foreach ($this->_callbacks as $callback) {
        $callback($record);
      }
    }

    return $record;
  }

  /**
   * 条件に一致する全てのレコードを取得します。
   *
   * @return array 条件に一致する全てのレコードを返します。
   */
  public function findAll()
  {
    $query = $this->buildSelectQuery($this->_conditions);
    $rs = $this->getConnection()->rawQuery($query);
    $records = array();
    $assocKey = NULL;

    if (isset($this->_conditions['options']['assocKey'])) {
      $assocKey = $this->_conditions['options']['assocKey'];
    }

    // ディスクロージャ形式のスコープを実行
    if (sizeof($this->_callbacks)) {
      // 配列のキーが指定されてる場合
      while ($record = $rs->read()) {
        foreach ($this->_callbacks as $callback) {
          $callback($record);
        }

        if ($assocKey === NULL) {
          $records[] = $record;
        } else {
          $records[$record->$assocKey] = $record;
        }
      }

    // 配列形式のスコープを実行
    } else {
      while ($record = $rs->read()) {
        if ($assocKey === NULL) {
          $records[] = $record;
        } else {
          $records[$record->$assocKey] = $record;
        }
      }
    }

    return $records;
  }

  /**
   * クライテリアにスコープを追加します。
   *
   * @param string $scopeName スコープ名。
   * @param array $variables スコープに割り当てる変数のリスト。
   * @return PHI_DatabaseCriteria クライテリアオブジェクトを返します。
   */
  public function add($scopeName, array $variables = array())
  {
    if (!isset($this->_scopes[$scopeName])) {
      $message = sprintf('Can\'t find scope ID. [%s]', $scopeName);
      throw new PHI_ConfigurationException($message);
    }

    $scope = $this->_scopes[$scopeName][0];

    if ($this->_scopes[$scopeName][1] !== NULL) {
      $this->_callbacks[] = $this->_scopes[$scopeName][1];
    }

    // スコープにクロージャが設定されてる場合は関数を実行
    if (is_object($scope)) {
      $variables = $this->getConnection()->getCommand()->quoteValues($variables);
      $scope = call_user_func_array($scope, $variables);
    }

    // 'select' の取得
    if (isset($scope['select']) && strlen($scope['select'])) {
      $this->_conditions['select'] = $scope['select'];
    }

    // 'select' の取得
    if (isset($scope['from']) && strlen($scope['from'])) {
      $this->_conditions['from'] = $scope['from'];
    }

    // 'where' の取得
    if (isset($scope['where']) && strlen($scope['where'])) {
      $this->_conditions['where'][] = array($scope['where'], 'AND');
    }

    // 'group' の取得
    if (isset($scope['group']) && strlen($scope['group'])) {
      $this->_conditions['group'] = $scope['group'];
    }

    // 'having' の取得
    if (isset($scope['having']) && strlen($scope['having'])) {
      $this->_conditions['having'] = $scope['having'];
    }

    // 'order' の取得
    if (isset($scope['order']) && strlen($scope['order'])) {
      $this->_conditions['order'] = $scope['order'];
    }

    // 'limit' の取得
    if (isset($scope['limit']) && strlen($scope['limit'])) {
      $this->_conditions['limit'] = $scope['limit'];
    }

    // 'offset' の取得
    if (isset($scope['offset']) && strlen($scope['offset'])) {
      $this->_conditions['offset'] = $scope['offset'];
    }

    // 'options' の取得
    if (isset($scope['options']) && is_array($scope['options'])) {
      $this->_conditions['options'] = $scope['options'];
    }

    return $this;
  }
}
