<?php
/**
 * @package actions
 */
class GenerateDAOFormAction extends PHI_Action
{
  public function execute()
  {
    $form = $this->getForm();
    $view = $this->getView();

    $config = PHI_Config::getApplication();
    $namespaceList = array();

    if (!$config->hasName('database')) {
      throw new PHI_ParseException('データベース接続情報が未定義です。(config/application.yml)');
    }

    foreach ($config->get('database') as $name => $values) {
      $namespaceList[$name] = $name;
    }

    if ($form->hasName('namespace')) {
      $dataSource = $form->get('namespace');
    } else {
      $dataSource = key($namespaceList);
    }

    $view->setAttribute('namespaceList', $namespaceList);
    $conn = NULL;

    try {
      $command = $this->getDatabase()->getConnection($dataSource)->getCommand();
      $view->setAttribute('tables', $command->getTables());

      $array = array();
      $selected = array();

      $array['dao'] = 'DAO';
      $array['entity'] = 'エンティティ';

      foreach ($array as $name => $value) {
        array_push($selected, $name);
      }

      $view->setAttribute('createType', $array);

      // 基底クラスの指定
      $form->set('baseDAOClassName', 'PHI_DAO', FALSE);
      $form->set('baseEntityClassName', 'PHI_DatabaseEntity', FALSE);

    } catch (Exception $e) {
      throw new PHI_ParseException('データベースへの接続時にエラーが発生しました。');
    }

    return PHI_View::SUCCESS;
  }
}
