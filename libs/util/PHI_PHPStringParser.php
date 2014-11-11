<?php
/**
 * PHP コードを実行します。
 * <code>
 * $code = '<?php echo $greeting;';
 * $parser = new PHI_PHPStringParser($code, array('greeting' => 'Hello World!'));
 * $parser->execute();
 *
 * // 'Hello World!'
 * $parser->output();
 * </code>
 *
 * @package util
 */
class PHI_PHPStringParser
{
  /**
   * @var string
   */
  private $_code;

  /**
   * @var array
   */
  private $_variables = array();

  /**
   * @var string
   */
  private $_output;

  /**
   * コンストラクタ。
   *
   * @param string $code 実行するソースコード。
   * @param array $variables ソースコードに渡す変数のリスト。array('{変数名}' => '{変数値}) の形式で指定。'
   */
  public function __construct($code, array $variables = array())
  {
    $this->_code = $code;
    $this->_variables = $variables;
  }

  /**
   * パーサに設定されているソースコードを取得します。
   *
   * @return string パーサに設定されているソースコードを返します。
   */
  public function getCode()
  {
    return $this->_code;
  }

  /**
   * パーサに設定されている変数のリストを取得します。
   *
   * @return array パーサに設定されている変数のリストを返します。
   */
  public function getVariables()
  {
    return $this->_variables;
  }

  /**
   * ソースコードを実行します。
   * このメソッドはパースエラーが発生した場合に {@link PHI_ErrorHandler::invokeFatalError()} をコールします。
   *
   * @return mixed ソースコードが戻り値を持つ場は値を返します。
   * @throws PHI_ParseException ソースコード内で何らかの例外が発生した場合に発生。
   */
  public function execute()
  {
    $result = NULL;

    try {
      ob_start();

      $eval = function($parser, $code, $attributes) {
        // eval() 内で Fatal エラーが発生した場合に PHI_ErrorHandler::invokeFatalError() でエラーコードを取得する
        PHI_Object::getRegistry()->set('parseCode', $parser);

        extract($attributes);
        $result = eval('?' . '>' . $code);

        // eval() 内でパースエラーが発生した場合、後に続く処理は継続されるため、プログラムの実行を強制終了する
        // (Fatal エラー時は FALSE を返さずプログラムが強制終了される)
        if ($result === FALSE) {
          // PHI_ErrorHandler::invokeFatalError() メソッドが起動
          die();
        }

        return $result;
      };

      $result = $eval($this, $this->_code, $this->_variables);
      $this->_output = ob_get_clean();

    } catch (Exception $e) {
      $lines = explode("\n", PHI_StringUtils::replaceLinefeed($this->_code));
      $current = $lines[$e->getLine() - 1];
      $message = sprintf('%s (Line: %s) [%s]', $e->getMessage(), $e->getLine(), $current);

      $exception = new PHI_ParseException($message);
      $exception->setTrigger($this->_code, PHI_Exception::TRIGGER_CODE_TYPE_PHP, $e->getLine());

      throw $exception;
    }

    return $result;
  }

  /**
   * ソースコードの実行結果を出力します。
   * このメソッドは {@link execute()} の後にコールする必要があります。
   *
   */
  public function output()
  {
    echo $this->_output;
  }

  /**
   * ソースコードの実行結果を取得します。
   * このメソッドは {@link execute()} の後にコールする必要があります。
   *
   * @return string ソースコードの実行結果を返します。
   */
  public function fetch()
  {
    return $this->_output;
  }
}

