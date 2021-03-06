<?php
/**
 * コンソールコマンドで有効な引数とオプションを定義します。
 *
 * <code>
 * class HelloWorldCommand extends PHI_ConsoleCommand
 * {
 *   public function {@link PHI_ConsoleCommand::configure() configure}(PHI_ConsoleInputConfigure $configure)
 *   {
 *     // 引数とオプションの登録
 *     $configure->{@link addArgument}('greeting', PHI_ConsoleInputConfigure::INPUT_REQUIRED)
 *       ->{@link addOption}('foo', PHI_ConsoleInputConfigure::OPTION_VALUE_NONE)
 *       ->{@link addOption}('bar', PHI_ConsoleInputConfigure::OPTION_VALUE_HAVE);
 *   }
 *
 *   // './phic HelloWorld hello -foo -bar=baz' コマンドを実行した場合
 *   public function {@link PHI_ConsoleCommand::execute() execute}()
 *   {
 *     $input = $this->getInput();
 *
 *     // 'hello'
 *     $greeting = $input->getArgument('greeting');
 *
 *     // TRUE
 *     $foo = $input->hasOption('foo');
 *
 *     // 'baz'
 *     $bar = $input->getOption('bar');
 *   }
 * }
 * </code>
 *
 * @package console
 */
class PHI_ConsoleInputConfigure extends PHI_Object
{
  /**
   * 引数の要求。(必須)
   */
  const INPUT_REQUIRED = 1;

  /**
   * 引数の要求。(任意)
   */
  const INPUT_OPTIONAL = 2;

  /**
   * オプション形式。(値を持つ)
   */
  const OPTION_VALUE_HAVE = 1;

  /**
   * オプション形式。(値を持たない)
   */
  const OPTION_VALUE_NONE = 2;

  /**
   * @var array
   */
  private $_arguments = array();

  /**
   * @var array
   */
  private $_options = array();

  /**
   * コマンドで有効な引数を追加します。
   * 引数は追加した順序で名前がバインドされる点に注意して下さい。
   * 例えば {@link PHI_ConsoleCommand::configure()} で次のコードが定義されているとします。
   * <code>
   * $configure->addArgument('foo')->addArgument('bar');
   * </code>
   * './phic {command_path} one two' コマンドを実行すると結果は次のようになります。
   * <code>
   * // 'one'
   * $input->getArgument('foo');
   *
   * // 'two'
   * $input->getArgument('bar');
   * </code>
   *
   * @param string $name 引数の名前。
   * @param int $type 引数が必須か任意かの指定。
   * @return PHI_ConsoleInputConfigure PHI_ConsoleInputConfigure オブジェクトを返します。
   */
  public function addArgument($name, $type = self::INPUT_REQUIRED)
  {
    $this->_arguments[$name] = array('type' => $type);

    return $this;
  }

  /**
   * コマンドで有効な引数のリストを取得します。
   *
   * @return array コマンドで有効な引数のリストを返します。
   */
  public function getArguments()
  {
    return $this->_arguments;
  }

  /**
   * コマンドで有効なオプションを追加します。
   * <code>
   * // コマンドに '--with-dir=/tmp' というオプションが指定された場合、'/tmp' が存在しない場合は {@link PHI_ConsoleInput::validate() バリデート} の時点で {@link InvalidArgumentException} をスローする
   * $configure->addOption('with-dir', PHI_ConsoleInputConfigure::OPTION_VALUE_HAVE, function($value, &$message) {
   *   return is_dir($value);
   * });
   * </code>
   *
   * @param string $name オプションの名前。
   * @param int $type 引数が値を持つか持たないかの指定。
   * @param callback $callback オプション値を検証するコールバック関数。
   *   o 第 1 引数: コンソールから渡されたオプションの値。
   *   o 第 2 引数: 値が不正な場合に例外に出力するメッセージを参照渡しで指定。(オプション)
   *   コールバック関数はオプションの値が正常な範囲内であれば TRUE、不正な値であれば FALSE を返すよう実装します。
   * @return PHI_ConsoleInputConfigure PHI_ConsoleInputConfigure オブジェクトを返します。
   */
  public function addOption($name, $type = self::OPTION_VALUE_NONE, $callback = NULL)
  {
    $this->_options[$name] = array(
      'type' => $type,
      'callback' => $callback
    );

    return $this;
  }

  /**
   * コマンドで有効なオプションのリストを取得します。
   *
   * @return array コマンドで有効なオプションのリストを返します。
   */
  public function getOptions()
  {
    return $this->_options;
  }
}
