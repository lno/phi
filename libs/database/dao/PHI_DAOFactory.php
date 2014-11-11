<?php
/**
 * データベースを操作するための DAO オブジェクトを生成します。
 *
 * @package database.dao
 */
class PHI_DAOFactory extends PHI_Object
{
  /**
   * コンストラクタ。
   */
  private function __construct()
  {}

  /**
   * DAO オブジェクトを生成します。
   *
   * @param string $name DAO の名前、またはクラス名。
   * @return PHI_DAO {@link PHI_DAO} を実装した DAO のインスタンスを返します。
   */
  public static function create($name)
  {
    static $instance = array();

    if (strcmp(substr($name, -3), 'DAO') != 0) {
      $name .= 'DAO';
    }

    if (empty($instance[$name])) {
      $instance[$name] = new $name;
    }

    return $instance[$name];
  }
}
