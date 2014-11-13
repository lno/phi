<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

define('PHI_ROOT_DIR', dirname(__DIR__));
define('PHI_LIBS_DIR', sprintf('%s/libs', PHI_ROOT_DIR));
define('PHI_SKELETON_DIR', sprintf('%s/skeleton', PHI_ROOT_DIR));
define('PHI_BLANK_APP_DIR', sprintf('%s/blank_application', PHI_SKELETON_DIR));

require_once PHI_ROOT_DIR . '/vendors/spyc/spyc.php';
require_once PHI_LIBS_DIR . '/kernel/loader/PHI_BootLoader.php';
require_once PHI_LIBS_DIR . '/console/PHI_ANSIGraphic.php';
require_once PHI_LIBS_DIR . '/console/PHI_ConsoleDialog.php';
require_once PHI_LIBS_DIR . '/console/PHI_ConsoleInput.php';
require_once PHI_LIBS_DIR . '/console/PHI_ConsoleInputConfigure.php';
require_once PHI_LIBS_DIR . '/console/PHI_ConsoleOutput.php';

$command = new PHI_CommandExecutor();
$command->parse();
$command->execute();

class PHI_CommandExecutor
{
  /**
   * @var PHI_ConsoleInput
   */
  private $_input;
  /**
   * @var PHI_ConsoleOutput
   */
  private $_output;
  private $_dialog;
  private $_currentPath;

  public function parse()
  {
    $input = new PHI_ConsoleInput();
    $input->parse();

    $dialog = $input->getDialog();
    $dialog->setSendFormat('> ', ': ');

    $configure = new PHI_ConsoleInputConfigure();
    $configure->addArgument('command', PHI_ConsoleInputConfigure::INPUT_OPTIONAL);
    $configure->addArgument('argument1', PHI_ConsoleInputConfigure::INPUT_OPTIONAL);

    $input->validate($configure);

    $output = new PHI_ConsoleOutput();
    $output->setErrorFormat('ERROR: ');

    $this->_input = $input;
    $this->_output = $output;
    $this->_currentPath = getcwd();
  }

  private function declareAppRootDir($command)
  {
    $result = FALSE;

    switch ($command) {
      case 'create-project':
        $result = TRUE;
        break;
      case 'add-action':
      case 'add-command':
      case 'add-module':
      case 'add-theme':
      case 'clear-cache':
      case 'cc':
      case 'install-database-cache':
      case 'install-database-session':
      case 'install-demo-app':
        $appRootDir = $this->findAppRootDir($this->_currentPath);
        if ($appRootDir) {
          $result = TRUE;
          define('APP_ROOT_DIR', $appRootDir);
        }
        break;
      case 'compress':
      case 'deploy':
      case 'generate-api':
      case 'help':
      case 'install-path':
      case 'version':
        $result = TRUE;
        define('APP_ROOT_DIR', NULL);
        break;
    }

    return $result;
  }

  public function execute()
  {
    $command = $this->_input->getArgument('command');
    $result = $this->declareAppRootDir($command);

    if ($result) {
      PHI_BootLoader::startConsoleCommand();

      switch ($command) {
        case 'add-action':
          $this->executeAddAction();
          break;
        case 'add-command':
          $this->executeAddCommand();
          break;
        case 'add-module':
          $this->executeAddModule();
          break;
        case 'add-theme':
          $this->executeAddTheme();
          break;
        case 'clear-cache':
        case 'cc':
          $this->executeClearCache();
          break;
        case 'create-project':
          $this->executeCreateProject();
          break;
        case 'compress':
          $this->executeCompress();
          break;
        case 'deploy': // フレームワーク開発者用
          $this->executeDeploy();
          break;
        case 'generate-api':
          $this->executeGenerateAPI();
          break;
        case 'help':
          $this->executeHelp();
          break;
        case 'install-database-cache':
          $this->executeInstallDatabaseCache();
          break;
        case 'install-database-session':
          $this->executeInstallDatabaseSession();
          break;
        case 'install-path':
          $this->executeInstallPath();
          break;
        case 'install-demo-app':
          $this->executeInstallDemoApp();
          break;
        case 'version':
          $this->executeVersion();
          break;
      }
    } else if (strlen($command)) {
      $message = sprintf('Unknown command. [%s]', $command);
      $this->_output->errorLine($message);
      $this->_output->writeBlankLines(1);

      $this->executeHelp();

    } else {
      $this->executeHelp();
    }
  }

  private function findAppRootDir($currentPath)
  {
    $result = FALSE;

    $searchPath = sprintf('%s%sconfig%senv.php',
      $currentPath,
      DIRECTORY_SEPARATOR,
      DIRECTORY_SEPARATOR);

    if (is_file($searchPath)) {
      $result = $currentPath;

    } else {
      if (($pos = strrpos($currentPath, DIRECTORY_SEPARATOR)) !== FALSE) {
        $currentPath = substr($currentPath, 0, $pos);
        $result = $this->findAppRootDir($currentPath);

      } else {
        $this->_output->errorLine('Can\'t find the application root directory.');
      }
    }

    return $result;
  }

  private function findModuleDirectory($currentPath)
  {
    $result = FALSE;
    $actionPath = $currentPath . DIRECTORY_SEPARATOR . 'actions';

    if (is_dir($actionPath)) {
      $result = $currentPath;

    } else {
      if (($pos = strrpos($currentPath, DIRECTORY_SEPARATOR)) !== FALSE) {
        $currentPath = substr($currentPath, 0, $pos);
        $result = $this->findModuleDirectory($currentPath);

      } else {
        $this->_output->errorLine('Can\'t find the module directory.');
      }
    }

    return $result;
  }

  private function executeCreateProject()
  {
    $dialog = $this->_input->getDialog();

    // インストールパスの指定
    do {
      $message = sprintf('Install path [%s]: ', $this->_currentPath);
      $this->_output->write($message);
      $installPath = trim(fgets(STDIN));

      if (dirname($installPath) === '.') {
        $this->_output->errorLine('Directory name is invalid.');

      } else {
        break;
      }

    } while (TRUE);

    if (strlen($installPath) == 0) {
      $installPath = $this->_currentPath;

    } else {
      $installPath = realpath(rtrim($installPath, '\'"\\/'));
    }

    $installPath = str_replace('\\', '/', $installPath);
    $currentPath = substr($installPath, strrpos($installPath, '/') + 1);

    // プロジェクト名の指定
    $message = sprintf('Project name [%s]', $currentPath);
    $projectName = $dialog->send($message);

    if (strlen($projectName) == 0) {
      $projectPath = $installPath;

    } else {
      if (substr($installPath, -1) === '/') {
        $projectPath = $installPath . $projectName;
      } else {
        $projectPath = sprintf('%s/%s', $installPath, $projectName);
      }
    }

    define('APP_ROOT_DIR', $projectPath);
    $result = FALSE;

    if (is_dir(APP_ROOT_DIR)) {
      $message = 'Directory already exists. Are you sure to overwrite it? (Y/N)';
      $result = $dialog->sendChoice($message, array('y', 'n'), FALSE);

    } else {
      if (PHI_FileUtils::createDirectory(APP_ROOT_DIR, 0755, TRUE)) {
        $result = TRUE;

      } else {
        $message = sprintf('Failed to create directory. [%s]', APP_ROOT_DIR);
        $this->_output->writeLine($message);
      }
    }

    if ($result) {
      // VCS の設定
      $message = 'Do you want to create a .gitkeep to empty directory? (Y/N)';

      if ($dialog->sendConfirm($message)) {
        $isCreateGitkeep = TRUE;
      } else {
        $isCreateGitkeep = FALSE;
      }

      // スケルトンディレクトリのコピー
      $options = array('recursive' => TRUE, 'hidden' => TRUE);
      PHI_FileUtils::copy(PHI_BLANK_APP_DIR, APP_ROOT_DIR, $options);

      // phic コマンドの実行権限付与
      $path = APP_ROOT_DIR . '/console/phic';
      chmod($path, 0775);

      // .htaccessのコピー
      $sourcePath = PHI_BLANK_APP_DIR . '/webroot/.htaccess';
      $destinationPath = APP_ROOT_DIR . '/webroot/.htaccess';
      copy($sourcePath, $destinationPath);

      $sourcePath = PHI_BLANK_APP_DIR . '/webroot/cpanel/.htaccess';
      $destinationPath = APP_ROOT_DIR . '/webroot/cpanel/.htaccess';
      copy($sourcePath, $destinationPath);

      // env.phpのコピー
      $sourcePath = PHI_BLANK_APP_DIR . '/config/env.php';
      $destinationPath = APP_ROOT_DIR . '/config/env.php';

      $contents = str_replace('{%PHI_ROOT_DIR%}', PHI_ROOT_DIR, file_get_contents($sourcePath));
      file_put_contents($destinationPath, $contents);

      // パーミッションの変更
      $cacheDir = APP_ROOT_DIR . '/cache';
      chmod($cacheDir, 0775);

      $logsDir = APP_ROOT_DIR . '/logs';
      chmod($logsDir, 0775);

      $tmpDir = APP_ROOT_DIR . '/tmp';
      chmod($tmpDir, 0775);

      // デフォルトモジュールの作成
      $moduleName = $this->executeAddModule(TRUE);

      // application.yml の書き換え
      // Spyc::YAMLDump() を使うと可視性が下がるのでここでは使用しない
      $path = APP_ROOT_DIR . '/config/application.yml';

      $secretKey = hash('sha1', uniqid(mt_srand(), TRUE));
      $password = PHI_StringUtils::buildRandomString(8, PHI_StringUtils::STRING_CASE_LOWER|PHI_StringUtils::STRING_CASE_NUMERIC);

      $contents = file_get_contents($path);
      $contents = str_replace('"{%SECRET_KEY%}"', $secretKey, $contents);
      $contents = str_replace('"{%CPANEL.PASSWORD%}"', $password, $contents);
      $contents = str_replace('"{%MODULE.ENTRY%}"', $moduleName, $contents);

      if ($isCreateGitkeep) {
        $replaceGitKeep = 'TRUE';
      } else {
        $replaceGitKeep = 'FALSE';
      }

      $contents = str_replace('"{%REPOSITORY.GITKEEP%}"',  $replaceGitKeep, $contents);

      file_put_contents($path, $contents);

      // routes.yml の書き換え
      $path = APP_ROOT_DIR . '/config/routes.yml';

      $contents = file_get_contents($path);
      $contents = str_replace('"{%MODULE.ENTRY%}"', $moduleName, $contents);
      file_put_contents($path, $contents);

      // 設定情報確認アクションのコピー
      $sourcePath = PHI_SKELETON_DIR . '/setup_info';
      $destinationPath = APP_ROOT_DIR . '/modules/' . $moduleName;

      $options = array('recursive' => TRUE);
      PHI_FileUtils::copy($sourcePath, $destinationPath, $options);

      $contents = PHI_FileUtils::readFile($sourcePath . '/actions/IndexAction.php');
      $contents = str_replace('{%PACKAGE_NAME%}', $moduleName . '.actions', $contents);
      PHI_FileUtils::writeFile($destinationPath . '/actions/IndexAction.php', $contents);

      $message = sprintf('Project installation is complete. [%s]', APP_ROOT_DIR);
      $this->_output->writeLine($message);

      $message = 'Do you want to install demo application? (Y/N)';
      $result = $dialog->sendConfirm($message);

      if ($result) {
        $this->executeInstallDemoApp();
      }

      // .gitkeep を作成しない場合は APP_ROOT_DIR から除外する
      if (!$isCreateGitkeep) {
        $files = PHI_FileUtils::search(APP_ROOT_DIR, '.gitkeep');

        foreach ($files as $file) {
          unlink($file);
        }

      } else {
        $file = APP_ROOT_DIR . '/modules/.gitkeep';
        unlink($file);
      }
    }
  }

  private function executeInstallDemoApp()
  {
    $demoDirectory = PHI_ROOT_DIR . '/webapps/demo';
    $options = array('recursive' => TRUE);
    PHI_FileUtils::copy($demoDirectory, APP_ROOT_DIR, $options);

    // site.yml のマージ
    $array1 = Spyc::YAMLLoad(APP_ROOT_DIR . '/config/site.yml');
    $array2 = Spyc::YAMLLoad($demoDirectory . '/config/site_merge.yml');

    $config = PHI_ArrayUtils::merge($array1, $array2);

    $custom = PHI_Config::createCustomFile('site');
    $custom->setArray($config);
    $custom->update();

    PHI_FileUtils::deleteFile('config/site_merge.yml');

    // global_helpers.yml のマージ
    $array1 = Spyc::YAMLLoad(APP_ROOT_DIR . '/config/global_helpers.yml');
    $array2 = Spyc::YAMLLoad($demoDirectory . '/config/global_helpers_merge.yml');

    $config = PHI_ArrayUtils::merge($array1, $array2);

    $custom = PHI_Config::createCustomFile('global_helpers');
    $custom->setArray($config);
    $custom->update();

    PHI_FileUtils::deleteFile('config/global_helpers_merge.yml');

    $message = sprintf("Demo application install completed.\n"
      ."  - %s%sdemo-front\n"
      ."  - %s%sdemo-admin\n",
      APP_ROOT_DIR,
      DIRECTORY_SEPARATOR,
      APP_ROOT_DIR,
      DIRECTORY_SEPARATOR);
    $this->_output->write($message);
  }

  private function executeAddModule($isDefaultModule = FALSE)
  {
    $moduleName = NULL;
    $dialog = $this->_input->getDialog();

    do {
      if ($isDefaultModule) {
        $moduleName = $dialog->send('Create default module name [entry]');
      } else {
        $moduleName = $dialog->send('Create module name', TRUE);
      }

      if ($isDefaultModule && strlen($moduleName) == 0) {
        $moduleName = 'entry';
      }

      try {
        $deployFiles = PHI_CoreUtils::addModule($moduleName);
        $deployFiles = implode("\n  - ", $deployFiles);

        $message = sprintf("Create module is complete.\n  - %s", $deployFiles);
        $this->_output->writeLine($message);

        if (!$isDefaultModule) {
          $this->executeClearCache(FALSE);
        }

        break;

      } catch (Exception $e) {
        $this->_output->errorLine($e->getMessage());
      }

    } while (TRUE);

    return $moduleName;
  }

  private function executeAddAction()
  {
    $moduleDirectory = $this->findModuleDirectory($this->_currentPath);

    if ($moduleDirectory) {
      $moduleName = basename($moduleDirectory);
      $dialog = $this->_input->getDialog();

      do {
        $message = 'Add action name (e.g. \'{package_name}/HelloWorld\')';
        $response = $dialog->send($message, TRUE);
        $parser = $this->parseActionAndCommandArgument($response);

        try {
          $deployFiles = PHI_CoreUtils::addAction($moduleName, $parser->actionName, $parser->packageName);
          $deployFiles = implode("\n  - ", $deployFiles);

          $message = sprintf("Create action is complete.\n  - %s", $deployFiles);
          $this->_output->writeLine($message);
          break;

        } catch (Exception $e) {
          $this->_output->errorLine($e->getMessage());
        }

      } while (TRUE);
    } // end if
  }

  private function parseActionAndCommandArgument($argument)
  {
    $packageName = '/';
    $actionName = NULL;

    if (($pos = strrpos($argument, '/')) === FALSE) {
      $actionName = $argument;

    } else {
      $packageName = substr($argument, 0, $pos);

      if (substr($packageName, 0, 1) !== '/') {
        $packageName = '/' . $packageName;
      }

      $actionName = substr($argument, $pos + 1);
    }

    $parser = new stdClass();
    $parser->packageName = $packageName;
    $parser->actionName = $actionName;

    return $parser;
  }

  private function executeAddCommand()
  {
    $message = 'Add command name (e.g. \'{package_name}/HelloWorld\')';
    $dialog = $this->_input->getDialog();

    do {
      $response = $dialog->send($message, TRUE);
      $parser = $this->parseActionAndCommandArgument($response);

      try {
        $deployFile = PHI_CoreUtils::addCommand($parser->actionName, $parser->packageName);
        $message = sprintf("Create command is complete.\n  - %s", $deployFile);

        $this->_output->writeLine($message);

        break;

      } catch (Exception $e) {
        $this->_output->errorLine($e->getMessage());
      }

    } while (TRUE);
  }

  private function executeAddTheme()
  {
    $themeConfig = PHI_Config::getApplication()->get('theme');
    $dialog = $this->_input->getDialog();

    if (isset($themeConfig['basePath'])) {
      if (PHI_FileUtils::isAbsolutePath($themeConfig['basePath'])) {
        $basePath = $themeConfig['basePath'];
      } else {
        $basePath = APP_ROOT_DIR . DIRECTORY_SEPARATOR . $themeConfig['basePath'];
      }

      $basePathConfig = $basePath;

    } else {
      $basePath = sprintf('%s%stheme', APP_ROOT_DIR, DIRECTORY_SEPARATOR);
      $basePathConfig = 'theme';
    }

    $isCustomPath = FALSE;

    // テーマディレクトリの作成
    if (!is_dir($basePath)) {
      do {
        $message = sprintf('Create theme directory. [%s]', $basePath);
        $response = $dialog->send($message);

        try {
          if (strlen($response)) {
            if (PHI_FileUtils::isAbsolutePath($response)) {
              $createPath = $response;
            } else {
              $createPath = APP_ROOT_DIR . DIRECTORY_SEPARATOR . $response;
            }

          } else {
            $createPath = $basePath;
          }

          PHI_FileUtils::createDirectory($createPath);
          $message = sprintf('  - %s', $createPath);
          $this->_output->writeLine($message);

          $basePath = $createPath;

          if (strpos($basePath, APP_ROOT_DIR) === 0) {
            $basePathConfig = substr($basePath, strlen(APP_ROOT_DIR) + 1);
          } else {
            $basePathConfig = $basePath;
          }

          break;

        } catch (Exception $e) {
          $this->_output->errorLine($e->getMessage());
        }

      } while (TRUE);
    }

    $basePath = PHI_FileUtils::buildAbsolutePath($basePath);
    $modules = array();

    do {
      $themeName = $dialog->send('Create theme name', TRUE);
      $themePath = sprintf('%s%s%s', $basePath, DIRECTORY_SEPARATOR, $themeName);

      if (is_dir($themePath)) {
        $this->_output->writeLine('WARNING: This theme is already exists.');
      }

      if ($themeName) {
        $message = 'Assign modules (e.g. front,backend,... or \'*\')';
        $response = $dialog->send($message, TRUE);

        if ($response === '*') {
          $moduleNames = PHI_CoreUtils::getModuleNames();
          print_r($moduleNames);

        } else {
          $moduleNames = explode(',', $response);
        }

        if (sizeof($moduleNames)) {
          $modules = array_unique($moduleNames);
          $files = NULL;

          try {
            $files = PHI_CoreUtils::addTheme($basePath, $themeName, $modules);
            $files = implode("\n  - ", $files);

            $message = sprintf("Create files:\n  - %s", $files);
            $this->_output->writeLine($message);

            break;

          } catch (Exception $e) {
            $this->_output->errorLine($e->getMessage());
          }

        } // end if

      } // end if

    } while (TRUE);

    $message = sprintf("Create theme is complete.\n"
      ."Please add settings to the file.\n"
      ."%s"
      ."{config/application.yml}\n"
      ."theme:\n"
      ."  name: %s\n"
      ."  basePath: %s\n"
      ."  modules: \n    - %s\n"
      ."%s",
    $this->_output->getSeparator(),
    $themeName,
    $basePathConfig,
    implode("\n    - ", $modules),
    $this->_output->getSeparator());

    $this->_output->write($message);
  }

  private function executeClearCache($output = TRUE)
  {
    PHI_CoreUtils::clearCache();

    if ($output) {
      $this->_output->writeLine('Clear cache completed.');
    }
  }

  private function executeCompress()
  {
    require PHI_ROOT_DIR . '/command/classes/PHI_CodeCompressor.php';

    $compressor = new PHI_CodeCompressor();
    $compressor->execute();
  }

  private function executeInstallDatabaseCache()
  {
    $dataSourceId = $this->getInstallDataSourceId();

    if ($this->createTable('cache/ddl.yml', $dataSourceId)) {
      $separator = $this->_output->getSeparator();

      $message = sprintf("Create database cache is complete.\n\n"
        ."Please add settings to the file.\n"
        ."%s"
        ."{config/application.yml}\n"
        ."cache:\n"
        ."  database:\n"
        ."    dataSource: %s\n"
        ."%s\n"
        ."Use:\n"
        ."%s"
        ."\$cache = PHI_CacheManager::getInstance(PHI_CacheManager::CACHE_TYPE_DATABASE);\n"
        ."\$cache->set('foo', \$data);\n"
        ."echo \$cache->get('foo');\n"
        ."%s",
        $separator,
        $dataSourceId,
        $separator,
        $separator,
        $separator);
      $this->_output->write($message);
    }
  }

  private function executeInstallDatabaseSession()
  {
    $dataSourceId = $this->getInstallDataSourceId();

    if ($this->createTable('session/ddl.yml', $dataSourceId)) {
      $separator = $this->_output->getSeparator();

      $message = sprintf("Create database session is complete.\n\n"
        ."Please add settings to the file.\n"
        ."%s"
        ."{config/application.yml}\n"
        ."session:\n"
        ."  handler:\n"
        ."    class: PHI_DatabaseSessionHandler\n"
        ."    dataSource: %s\n"
        ."%s",
      $separator,
      $dataSourceId,
      $separator);

      $this->_output->write($message);
    }
  }

  private function getInstallDataSourceId()
  {
    $dataSourceId = $this->_input->getDialog()->send('Install data source of database. [default]');

    if (strlen($dataSourceId) == 0) {
      $dataSourceId = 'default';
    }

    return $dataSourceId;
  }

  private function createTable($path, $dataSourceId)
  {
    $result = FALSE;
    $appConfig = PHI_Config::get(PHI_Config::TYPE_DEFAULT_APPLICATION);

    $key = 'database.' . $dataSourceId;
    $connectConfig = $appConfig->get($key);

    if (!$connectConfig) {
      $message = sprintf('Definition of database can\'t be found. [%s]', $dataSourceId);
      $this->_output->errorLine($message);

    } else {
      // テーブルの作成
      $path = sprintf('%s/database/%s', PHI_SKELETON_DIR, $path);
      $data = Spyc::YAMLLoad($path);

      PHI_DIContainerFactory::initialize();

      if ($connectConfig) {
        $dsn = $connectConfig->get('dsn');
        $user = $connectConfig->get('user');
        $password = $connectConfig->get('password');

        $database = PHI_DatabaseManager::getInstance();
        $conn = $database->getConnectionWithConfig($dsn, $user, $password);
        $command = $conn->getCommand();

        foreach ($data['tables'] as $table) {
          if (!$command->existsTable($table['name'])) {
            $command->createTable($table);

            $message = sprintf("Create table %s.%s.", $dataSourceId, $table['name']);
            $this->_output->writeLine($message);
            $result = TRUE;

          } else {
            $message = sprintf('Table already exists. [%s.%s]', $dataSourceId, $table['name']);
            $this->_output->errorLine($message);
          }
        }

      } else {
        $this->_output->errorLine('Data source does not exists. [default]');
      }
    }

    return $result;
  }

  private function executeInstallPath()
  {
    $this->_output->writeLine(PHI_ROOT_DIR);
  }

  private function executeGenerateAPI()
  {
    $sourcePath = $this->_input->getArgument('argument1');
    $outputPath = $this->_input->getOption('output-dir');
    $excludes = $this->_input->getOption('excludes');

    if ($excludes !== NULL) {
      $excludes = explode(',', $excludes);
    } else {
      $excludes = array();
    }

    $title = $this->_input->getOption('title', 'Application API');

    if ($sourcePath === NULL) {
      $buffer = "USAGE: \n"
       ."  phi generate-api [ARGUMENT] [OPTIONS]\n\n"
       ."ARGUMENT:\n"
       ."  source-dir                 Source directory path. (e.g. 'phi generate-api /var/repos/project')\n\n"
       ."OPTIONS:\n"
       ."  --output-dir={output_path} Output directory path. (default: {source-dir}/api)\n"
       ."  --excludes={excludes}      List of exclude directories. (foo,bar,baz...)\n"
       ."  --title={title}            TItle of API.\n";

      $this->_output->write($buffer);

    } else {
      // 入力パスを取得
      $realSourcePath = realpath($sourcePath);

      if ($realSourcePath === FALSE) {
        $message = sprintf("Can't find the source path. [%s]", $sourcePath);
        $this->_output->errorLine($message);

      } else {
        // 出力パスの取得
        if ($outputPath === NULL) {
          $realOutputPath = sprintf('%s%sapi', $this->_currentPath, DIRECTORY_SEPARATOR);
        } else if (!PHI_FileUtils::isAbsolutePath($outputPath)) {
          $realOutputPath = sprintf('%s%s%s', $this->_currentPath, DIRECTORY_SEPARATOR, $outputPath);
        } else {
          $realOutputPath = $outputPath;
        }

        if (!is_dir($realOutputPath)) {
          PHI_FileUtils::createDirectory($realOutputPath);
        }

        $this->buildAPI($realSourcePath, $realOutputPath, $title, $excludes);
      }
    }
  }

  private function executeDeploy()
  {
    $outputPath = PHI_ROOT_DIR . '/api';
    $title = sprintf('phi %s API Reference', PHI_CoreUtils::getVersion(TRUE));
    $this->buildAPI(PHI_LIBS_DIR, $outputPath, $title, array(), TRUE);
  }

  private function buildAPI($sourcePath, $outputPath, $title, $excludes = array(), $buildCoreAPI = FALSE)
  {
    $this->_output->writeLine('Initializing API Generator...');

    PHI_DIContainerFactory::initialize();

    $this->_output->writeLine('Parsing of source code...');

    $generator = new PHI_APIGenerator($sourcePath);
    $generator->setExcludeDirectories($excludes);
    $generator->setTitle($title);
    $generator->setOutputDirectory($outputPath);
    $generator->make($buildCoreAPI);

    $this->_output->writeLine('Building API...');
    $generator->build();

    $this->_output->writeLine('Writing API...');
    $generator->write();

    $message = sprintf("Process was successful.\n  - %s", $outputPath);
    $this->_output->writeLine($message);
  }

  private function executeVersion()
  {
    $this->_output->writeLine(PHI_CoreUtils::getVersion());
  }

  private function executeHelp()
  {
    $buffer = "USAGE: \n"
     ."  phi [OPTIONS]\n\n"
     ."OPTIONS:\n"
     ."  add-action               Add action to current module.\n"
     ."                           If you want to use a skeleton template,\n"
     ."                           please edit '{APP_ROOT_DIR}/templates/html/skeleton.php'.\n"
     ."  add-command              Add command to current project.\n"
     ."  add-module               Add module to current project.\n"
     ."  add-theme                Add theme to current project.\n"
     ."  clear-cache [cc]         Clear the cache of all.\n"
     ."  create-project           Create new project.\n"
     ."  compress                 Compress source of framework.\n"
     ."  generate-api             Generate API from source code. \n"
     ."  help                     Show how to use the command.\n"
     ."  install-database-cache   Create a database cache table. (see: PHI_DatabaseCache class)\n"
     ."  install-database-session Create a database session table. (see: PHI_DatabaseSessionHandler class)\n"
     ."  install-demo-app         Install demo application.\n"
     ."  install-path             Get directory path of the framework.\n"
     ."  version                  Get version information.";

    $this->_output->writeLine($buffer);
  }
}

