<?php
/**
 * データベースの 1 レコードを表現するエンティティオブジェクトです。
 * プロパティに (データベースの) NULL 値や式 (NOW() 等) を割り当てたい場合、{@link PHI_DatabaseExpression} クラスを利用して下さい。
 *
 * <code>
 * $entity = PHI_DAOFactory::create({dao_name})->createEntity();
 * $entity->greetingId = 1;
 * $entity->message = 'Hello World!';
 * $entity->name = new PHI_DatabaseExpression::null();
 * $entity->registerDate = new PHI_DatabaseExpression('NOW()');
 * </code>
 *
 * @package database.entity
 */
abstract class PHI_DatabaseEntity extends PHI_Entity
{}
