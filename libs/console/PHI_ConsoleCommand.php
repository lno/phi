<?php
/**
 * コンソールアプリケーションのコマンド機能を提供します。
 * 全てのコマンドは PHI_ConsoleCommand クラスを継承する必要があります。
 * <code>
 * {console/commands/HelloWorldCommand.php}
 * class HelloWorldCommand extends PHI_ConsoleCommand
 * {
 *   public function execute()
 *   {
 *     $this->getOutput()->writeLine('Hello world!');
 *   }
 * }
 * </code>
 *
 * @package console
 */
abstract class PHI_ConsoleCommand extends PHI_Object
{
  /**
   * @var PHI_ConsoleInput
   */
  private $_input;

  /**
   * @var PHI_ConsoleOutput
   */
  private $_output;

  /**
   * コンストラクタ。
   *
   * @param PHI_ConsoleInput $input コンソール入力オブジェクト。
   * @param PHI_ConsoleOutput $output コンソール出力オブジェクト。
   */
  public function __construct(PHI_ConsoleInput $input, PHI_ConsoleOutput $output)
  {
    $this->_input = $input;
    $this->_output = $output;
  }

  /**
   * コンソール入力オブジェクトを取得します。
   *
   * @return PHI_ConsoleInput コンソール入力オブジェクトを取得します。
   */
  public function getInput()
  {
    return $this->_input;
  }

  /**
   * コンソール出力オブジェクトを取得します。
   *
   * @return PHI_ConsoleOutput コンソール出力オブジェクトを取得します。
   */
  public function getOutput()
  {
    return $this->_output;
  }

  /**
   * コマンドで利用可能な引数とオプションを定義します。
   * このメソッドは {@link execute()} の直前にコールされ、{@link PHI_ConsoleInput::validate()} メソッドによりパラメータの検証が行われます。
   *
   * @param PHI_ConsoleInputConfigure $configure コマンドで利用可能な引数とオプションを管理するオブジェクト。
   * @see PHI_ConsoleInputConfigure
   */
  public function configure(PHI_ConsoleInputConfigure $configure)
  {}

  /**
   * コマンドを実行します。
   */
  abstract public function execute();

  /**
   * データベースマネージャを取得します。
   *
   * @return PHI_DatabaseManager データベースマネージャを返します。
   */
  public function getDatabase()
  {
    return PHI_DatabaseManager::getInstance();
  }
}
