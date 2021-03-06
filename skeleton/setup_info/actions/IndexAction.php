<?php
/**
 * @package modules.entry.actions
 */
class IndexAction extends PHI_Action
{
  public function execute()
  {
    $request = $this->getRequest();
    $messages = $this->getMessages();

    // PHP のバージョンチェック
    $requireVersion = '5.4.0';
    $currentVersion = phpversion();

    if (version_compare($requireVersion, $currentVersion, '>')) {
      $message = sprintf('PHP のバージョンは %s 以上である必要があります。インストールされているバージョンは %s です。',
        $requireVersion,
        $currentVersion);
      $messages->addError($message, 'php');
    }

    $failureDirectory = array();

    // cache ディレクトリの権限チェック
    if (PHI_FileUtils::getMode('cache') < 775) {
      $failureDirectory[] = 'cache';
    }

    // logs ディレクトリの権限チェック
    if (PHI_FileUtils::getMode('logs') < 775) {
      $failureDirectory[] = 'logs';
    }

    // tmp ディレクトリの権限チェック
    if (PHI_FileUtils::getMode('tmp') < 775) {
      $failureDirectory[] = 'tmp';
    }

    if (sizeof($failureDirectory)) {
      $message = sprintf('権限が不足しています。次のディレクトリの権限を 0775 に設定して下さい。[%s]', implode(', ', $failureDirectory));
      $messages->addError($message, 'permission');
    }

    // mod_rewrite の動作チェック
    if ($request->getRoute()->getRouteName() === 'rewriteTestRoute') {
      $this->getResponse()->write('SUCCESS');

      return PHI_View::NONE;

    } else {
      $path = array('route' => 'rewriteTestRoute');
      $requestUrl = PHI_FrontController::getInstance()->getRouter()->buildRequestPath($path, array(), TRUE);

      try {
        if (file_get_contents($requestUrl) !== 'SUCCESS') {
          throw new PHI_RequestException();
        }

      } catch (PHI_RequestException $e) {
        $messages->addError('mod_rewrite が正常に動作していない可能性があります。モジュールの設定を見直して下さい。', 'route');
      }
    }

    // データベースへの接続チェック
    try {
      $this->getDatabase()->getConnection();

    } catch (PDOException $e) {
      $messages->addError($e->getMessage(), 'database');
    }

    // cpanel の動作チェック
    if (!$request->getParameter('check')) {
      // cpanel のパスは固定なので PHI_RouteResolver::buildRequestPath() 経由でパスを算出しない
      $requestUrl = 'http://' . $request->getEnvironment('HTTP_HOST') . '/cpanel/connectTest';

      try {
        if (file_get_contents($requestUrl) !== 'SUCCESS') {
          throw new Exception();
        }

      } catch (Exception $e) {
        $messages->addError('コントロールパネルが表示できない可能性があります。', 'cpanel');
      }
    }

    // デモアプリケーションがインストールされているかチェック
    if (in_array('demo-front', PHI_CoreUtils::getModuleNames())) {
      $this->getView()->setAttribute('hasDemoApp', TRUE);

      if ($messages->hasError('database')) {
        $messages->addError('データベースに接続できないため、デモアプリケーションを起動できません。', 'demo');
      }
    }

    return PHI_View::SUCCESS;
  }
}
