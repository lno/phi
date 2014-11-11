<?php
require PHI_LIBS_DIR . '/console/PHI_ConsoleCommand.php';
require PHI_LIBS_DIR . '/console/PHI_ConsoleInput.php';
require PHI_LIBS_DIR . '/console/PHI_ConsoleInputConfigure.php';
require PHI_LIBS_DIR . '/console/PHI_ConsoleOutput.php';

/**
 * コンソールアプリケーションのためのコマンドラインインタフェースを提供します。
 * コンソールで './phic' (Windows 環境の場合は ./phic.bat) を実行することでコマンドの起動方法を確認することができます。
 *
 * @package console
 */
class PHI_Console extends PHI_Object
{
  /**
   * オブザーバオブジェクト。
   * @var PHI_KernelEventObserver
   */
  private $_observer;

  /**
   * @var string
   */
  private $_commandName;

  /**
   * コンストラクタ。
   */
  private function __construct()
  {
    $this->_observer = new PHI_KernelEventObserver(PHI_BootLoader::BOOT_MODE_CONSOLE);
    $this->_observer->initialize();
  }

  /**
   * コンソールのインスタンスオブジェクトを取得します。
   *
   * @return PHI_Console コンソールのインスタンスオブジェクトを返します。
   */
  public static function getInstance()
  {
    static $instance;

    if ($instance === NULL) {
      $instance = new PHI_Console();
    }

    return $instance;
  }

  /**
   * コンソールアプリケーションを開始します。
   */
  public function start()
  {
    $input = new PHI_ConsoleInput();
    $input->parse();

    $commandPath = $input->getCommandPath();

    // コマンドパスが見つからない場合はヘルプを表示
    if ($commandPath !== NULL) {
      require $commandPath;

      $this->_commandName = $input->getCommandName(TRUE);
      $output = new PHI_ConsoleOutput();
      $configure = new PHI_ConsoleInputConfigure();

      if ($input->hasCoreOption('silent')) {
        $output->setSilentMode(TRUE);
      }

      $commandClass = new $this->_commandName($input, $output);
      $commandClass->configure($configure);

      $input->validate($configure);
      $commandClass->execute();

      $this->_observer->dispatchEvent('postProcess');

    } else {
      $this->showUsage();
    }
  }

  /**
   * 実行中のコマンド名を取得します。
   *
   * @return string 実行中のコマンド名を返します。
   */
  public function getCommandName()
  {
    return $this->_commandName;
  }

  /**
   * コンソールアプリケーションに関するヘルプを表示します。
   */
  public function showUsage()
  {
    if (stripos(PHP_OS, 'WIN') !== FALSE) {
      $commandName = 'phic';
    } else {
      $commandName = 'phic.bat';
    }

    $message = sprintf("USAGE:\n"
      ."  ./%s [PHIC_OPTIONS] [ARGUMENT] [COMMAND_OPTIONS]\n"
      ."\n"
      ."PHIC OPTIONS:\n"
      ."  --silent\n"
      ."    Hide all output messages.\n\n"
      ."  --help\n"
      ."    Show how to use the command.\n\n"
      ."ARGUMENT:\n"
      ."  {command_path} ({argument} {argument}...}\n"
      ."    Run the '{APP_ROOT_DIR}/console/commands/{command_path}Command.php' command.\n"
      ."    If you want to run 'foo/bar/BazCommand.php', please specified 'foo.bar.Baz' or file path.\n\n"
      ."  {autoload_id}:{command_path} ({argument} {argument}...)\n"
      ."    Run the command as defined in the 'autoload' path. (see application.yml)\n\n"
      ."COMMAND OPTIONS:\n"
      ."  --{option_name} (-{option_name})\n"
      ."    Set optional argument with key.\n\n"
      ."  --{option_name}={option_value} (-{option_name}={option_value})\n"
      ."    Set optional argument with key-value.",
      $commandName,
      $commandName);

    $output = new PHI_ConsoleOutput();
    $output->writeLine($message);
  }
}
