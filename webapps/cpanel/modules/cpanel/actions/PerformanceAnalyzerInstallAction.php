<?php
/**
 * @package actions
 */
class PerformanceAnalyzerInstallAction extends PHI_Action
{
  public function execute()
  {
    // リスナが宣言されているかチェック
    $dataSourceId = PHI_PerformanceListener::getDataSourceId();

    if ($dataSourceId) {
      $key = 'database.' . $dataSourceId;
      $config = PHI_Config::getApplication();

      if (!$config->hasName($key)) {
        $message = sprintf('config/application.yml にデータベース属性 \'database.%s\' が定義されていません。', $dataSourceId);
        $this->getMessages()->addError($message);

        return PHI_View::ERROR;
      }

      $path = PHI_ROOT_DIR . '/skeleton/database/performance_analyzer/ddl.yml';
      $data = PHI_Config::getCustomFile($path)->toArray();

      try {
        $command = $this->getDatabase()->getConnection($dataSourceId)->getCommand();

        foreach ($data['tables'] as $table) {
          $command->createTable($table);
        }

      } catch (PDOException $e) {
        $message = sprintf('解析ログテーブルの作成に失敗しました。[%s]', $e->getMessage());
        $this->getMessages()->addError($message);

        return PHI_View::ERROR;
      }

      $this->getMessages()->add('インストールに成功しました。');

    } else {
      $message = '設定ファイルにリスナが宣言されていません。';
      $this->getMessages()->addError($message);

      return PHI_View::ERROR;
    }

    return PHI_View::SUCCESS;
  }
}
