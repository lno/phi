<?php
/**
 * @package actions
 */
class GenerateDAOAction extends PHI_Action
{
  public function execute()
  {
    $form = $this->getForm();
    $view = $this->getView();

    $namespace = $form->get('namespace');
    $tables = $form->get('tables');
    $createType = $form->get('createType');
    $baseDAOClassName = $form->get('baseDAOClassName');
    $baseEntityClassName = $form->get('baseEntityClassName');

    $createEntity = false;
    $createDAO = false;

    if (in_array('entity', $createType)) {
      $createEntity = true;
    }

    if (in_array('dao', $createType)) {
      $createDAO = true;
    }

    $command = $this->getDatabase()->getConnection($namespace)->getCommand();

    $baseDirectory = PHI_ROOT_DIR . '/skeleton/database_classes';
    $requirePath = $baseDirectory . '/entity_class.php.tpl';
    $entityTemplate = PHI_FileUtils::readFile($requirePath);

    $requirePath = $baseDirectory . '/dao_class.php.tpl';
    $daoTemplate = PHI_FileUtils::readFile($requirePath);

    $entities = array();
    $dataAccessObjects = array();

    $tmpEntityDirectory = APP_ROOT_DIR . '/tmp/entity';

    if (!is_dir($tmpEntityDirectory)) {
      PHI_FileUtils::createDirectory($tmpEntityDirectory);
    }

    $tmpDaoDirectory = APP_ROOT_DIR . '/tmp/dao';

    if (!is_dir($tmpDaoDirectory)) {
      PHI_FileUtils::createDirectory($tmpDaoDirectory);
    }

    foreach ($tables as $tableName) {
      $pascalTableName = PHI_StringUtils::convertPascalCase($tableName);

      // エンティティクラスの生成
      if ($createEntity) {
        $fileName = $pascalTableName . 'Entity.php';

        $array = array();
        $array['absolute'] = $tmpEntityDirectory . '/' . $fileName;
        $array['relative'] = 'tmp/' . $fileName;
        $array['file'] = $fileName;

        $entities[] = $array;

        $fields = $command->getFields($tableName);
        $j = count($fields);

        $propertiesBuffer = null;

        for ($i = 0; $i < $j; $i++) {
          $column = strtolower($fields[$i]);
          $propertyName = PHI_StringUtils::convertCamelCase($column);
          $propertiesBuffer .= '  public $' . $propertyName . ";\n";
        }

        $from = array(
          '{%BASE_ENTITY_CLASS_NAME%}',
          '{%CLASS_NAME%}',
          '{%PROPERTIES%}'
        );
        $to = array(
          $baseEntityClassName,
          $pascalTableName . 'Entity',
          $propertiesBuffer
        );

        $classBuffer = str_replace($from, $to, $entityTemplate);

        PHI_FileUtils::writeFile($array['absolute'], $classBuffer);
      }

      // DAO クラスの生成
      if ($createDAO) {
        $fileName = $pascalTableName . 'DAO.php';

        $array = array();
        $array['absolute'] = $tmpDaoDirectory . '/' . $fileName;
        $array['relative'] = 'tmp/' . $fileName;
        $array['file'] = $pascalTableName . 'DAO.php';

        $dataAccessObjects[] = $array;
        $primaryKeys = $command->getPrimaryKeys($tableName);
        $primaryKeysString = implode(', ', PHI_ArrayUtils::appendEachString($primaryKeys, '\''));

        $from = array(
          '{%BASE_DAO_CLASS_NAME%}',
          '{%DATA_SOURCE_ID%}',
          '{%CLASS_NAME%}',
          '{%TABLE_NAME%}',
          '{%PRIMARY_KEYS%}'
        );
        $to = array(
          $baseDAOClassName,
          $namespace,
          $pascalTableName . 'DAO',
          $tableName,
          $primaryKeysString
        );

        $classBuffer = str_replace($from, $to, $daoTemplate);
        PHI_FileUtils::writeFile($array['absolute'], $classBuffer);
      }
    }

    $view->setAttribute('entities', $entities);
    $view->setAttribute('dataAccessObjects', $dataAccessObjects);

    return PHI_View::SUCCESS;
  }
}
