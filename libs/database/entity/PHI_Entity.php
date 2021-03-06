<?php
/**
 * ドメインモデルのデータを表現するオブジェクトです。
 *
 * <code>
 * $entity = new GreetingEntity();
 * $entity->message = 'Hello World!';
 * </code>
 *
 * @package database.entity
 */
abstract class PHI_Entity extends PHI_Object
{
  /**
   * コンストラクタ。
   *
   * @param array $fields {@link bindFields()} の項を参照。
   */
  public function __construct(array $fields = array())
  {
    if (sizeof($fields)) {
      $this->bindFields($fields);
    }
  }

  /**
   * セッターマジックメソッド。
   *
   * @param string $name フィールド名。
   * @param string $value フィールドに割り当てる値。
   * @throws RuntimeException 存在しないフィールドへアクセスした場合に発生。
   */
  public function __set($name, $value)
  {
    $name = PHI_StringUtils::convertSnakeCase($name, FALSE);

    if (property_exists($this, $name)) {
      $this->$name = $value;

    } else {
      $this->throwUndefinedException($name);
    }
  }

  /**
   * ゲッターマジックメソッド。
   *
   * @param string $name フィールド名。
   * @throws RuntimeException 存在しないフィールドを参照された場合に発生。
   */
  public function __get($name)
  {
    $name = PHI_StringUtils::convertSnakeCase($name);

    if (property_exists($this, $name)) {
      return $this->$name;

    } else {
      $this->throwUndefinedException($name);
    }
  }

  /**
   * @param string $fieldName
   */
  private function throwUndefinedException($fieldName)
  {
    $message = sprintf('Field does not exist in entity. [%s::$%s]',
      get_class($this),
      $fieldName);
    throw new RuntimeException($message);
  }

  /**
   * エンティティのフィールドにデータを割り当てます。
   *
   * @param array $fields エンティティに割り当てるデータ。
   *   array({field_name} => {field_value}) の形式で指定可能。
   * @throws RuntimeException 存在しないフィールドへアクセスした場合に発生。
   */
  public function bindFields(array $fields)
  {
    foreach ($fields as $name => $value) {
      $this->$name = $value;
    }
  }

  /**
   * エンティティデータを配列に変換します。
   *
   * @param bool $toSnakeCase 配列のキー名を snake_case 形式に変換する場合は TRUE を指定。
   * @return array エンティティデータを含む配列を返します。
   */
  public function toArray($toSnakeCase = TRUE)
  {
    $class = new ReflectionClass($this);
    $fields = $class->getProperties();
    $array = array();

    foreach ($fields as $field) {
      if ($field->isPublic()) {
        $fieldName = $field->getName();

        if ($toSnakeCase) {
          $assocName = PHI_StringUtils::convertSnakeCase($fieldName);
        } else {
          $assocName = $fieldName;
        }

        $array[$assocName] = $this->$fieldName;
      }
    }

    return $array;
  }

  /**
   * データフィールド名の一覧を取得します。
   *
   * @return array データフィールド名の一覧を返します。
   */
  public function getDataFieldNames()
  {
    $class = new ReflectionClass($this);
    $fields = $class->getProperties();

    $array = array();

    foreach ($fields as $field) {
      if ($field->isPublic()) {
        $array[] = PHI_StringUtils::convertSnakeCase($field->getName());
      }
    }

    return $array;
  }
}
