<?php
/**
 * {@link PHI_Database_Criteria クライテリア} で利用するスコープを定義します。
 *
 * @package database.criteria
 */
class PHI_DatabaseCriteriaScopes extends PHI_Object
{
  /**
   * @var array
   */
  private $_scopes = array();

  /**
   * スコープを追加します。
   *
   * @param string $scopeName スコープ名。
   * @param mixed $condition データの抽出条件を配列、またはクロージャ形式で指定。
   *   抽出条件に指定可能なキーは次の通り。
   *   <ul>
   *     <li>select: 'SELECT' 句。参照するフィールドを指定。未指定時は全てのカラムが取得対象となる。</li>
   *     <li>from: ''FROM' 句。テーブル名を指定。未指定の場合は {@link PHI_DAO::getTableName()} が参照される。</li>
   *     <li>where: 'WHERE' 句。抽出条件を指定。</li>
   *     <li>複数のスコープを add() で追加した場合、'where' は 'AND' 条件で結合されます。</li>
   *     <li>group: 'GROUP BY' 句。フィールドのグループ条件を指定。</li>
   *     <li>having: 'HAVING' 句。集計対象の条件を指定。</li>
   *     <li>order: 'ORDER BY' 句。ソート条件を指定。</li>
   *     <li>limit: 'LIMIT' 句。レコードの取得数を指定。</li>
   *     <li>offset: 'OFFSET' 句。レコードの取得開始位置を指定。</li>
   *     <li>options: その他のオプションを配列形式で指定。
   *       <ul>
   *         <li>assocKey: {@link PHI_DatabaseCriteria::findAll()} メソッドでレコードを返す際の配列キーを指定したフィールド値とする。</li>
   *         <li>dataSourceId: データソース ID。未指定時は {@link PHI_DAO::getDataSourceId()} が使用される。</li>
   *       </ul>
   *     </li>
   *   </ul>
   *   複数のスコープを add() で追加した場合、各キーは一番最後に追加した条件が有効となります。('where' 以外)
   * @param mixed $callback {@link PHI_DatabaseCriteria::find()} や {@link PHI_DatabaseCriteria::findAll()} メソッドで返されるレコードを加工するためのコールバック関数。
   *   <code>
   *   $scopes->add(
   *     'custom',
   *     NULL,
   *     function($record) {
   *       // レコードが持つ foo、bar の値を加算して baz フィールドに格納
   *       $record->baz = $record->foo + $record->bar;
   *     }
   *   );
   *
   *   $usersDAO = PHI_DAOFactory::create('Users');
   *   $record = $usersDAO->createCriteria()->find();
   *
   *   // 'foo' + 'bar' の加算値が格納されている
   *   $record->baz;
   *   </code>
   * @return PHI_DatabaseCriteriaScopes オブジェクト自身を返します。
   */
  public function add($scopeName, $condition, $callback = NULL)
  {
    $this->_scopes[$scopeName] = array($condition, $callback);

    return $this;
  }

  /**
   * 登録されている全てのスコープを取得します。
   *
   * @return array 登録されている全てのスコープを返します。
   */
  public function getScopes()
  {
    return $this->_scopes;
  }
}
