<?php
/**
 * 全てのヘルパの基底となるクラスです。
 * ヘルパのインスタンスは {@link PHI_HelperManager::getHelper()} から取得して下さい。
 *
 * global_helpers.yml の設定例:
 * <code>
 * {ヘルパ ID}:
 *   # 実装クラス名。
 *   class:
 *
 *   # テンプレートに割り当てるヘルパインスタンスの変数名。
 *   # 未指定の場合はヘルパ ID が変数名として使用される。
 *   assign:
 *
 *   # テンプレート変数の自動割り当てを行うかどうかの指定。
 *   # TRUE を指定した場合は出力テンプレート決定時に自動的にインスタンスが割り当てられる。
 *   bind: TRUE
 * </code>
 *
 * @package view.helper
 */
abstract class PHI_Helper extends PHI_WebApplication
{
  /**
   * ヘルパで使用する基底のルータ名。
   * @var string
   */
  protected static $_baseRouteName;

  /**
   * ヘルパが持つデフォルト属性。
   * @var array
   */
  protected static $_defaultValues = array();

  /**
   * ビューオブジェクト。
   * @var PHI_View。
   */
  protected $_currentView;

  /**
   * ヘルパ属性。
   * @var PHI_ParameterHolder
   */
  protected $_config;

  /**
   * {@link PHI_RouteResolver} オブジェクト。
   * @var PHI_RouteResolver
   */
  protected $_router;

  /**
   * コンストラクタ。
   *
   * @param PHI_View $currentView ヘルパを適用するビューオブジェクト。
   * @param array $config ヘルパ属性。
   */
  public function __construct(PHI_View $currentView, array $config = array())
  {
    parent::__construct();

    $this->_currentView = $currentView;
    $this->_config = new PHI_ParameterHolder(PHI_ArrayUtils::merge(static::$_defaultValues, $config));
    $this->_router = PHI_FrontController::getInstance()->getRouter();
  }

  /**
   * ヘルパの出力を制御するデフォルトパラメータのリストを取得します。
   *
   * @return array ヘルパの出力を制御するデフォルトパラメータのリストを返します。
   */
  public static function getDefaultValues()
  {
    return self::$_defaultValues;
  }

  /**
   * ヘルパを初期化します。
   * このメソッドは {@link PHI_View::loadHelpers()} がコールされた直後に実行されます。
   *
   * @see PHI_View::execute()
   */
  public function initialize()
  {}

  /**
   * パスの生成に用いる基底のルータを設定します。
   *
   * @param string $baseRouter ルータ名。
   * @throws PHI_ConfigurationException 指定されたルータが見つからない場合に発生。
   */
  public static function setBasePathRouter($baseRouter)
  {
    $config = PHI_Config::getRoutes();

    if ($config->hasName($baseRouter)) {
      self::$_baseRouteName = $baseRouter;

    } else {
      $message = sprintf('Can\'t find router. [%s]', $baseRouter);
      throw new PHI_ConfigurationException($message);
    }
  }

  /**
   * {@link setBasePathRouter()} メソッドで設定したルータを用いてリクエストパスを生成します。
   *
   * @param mixed $path {@link PHI_RouteResolver::buildRequestPath()} メソッドを参照。
   * @param array $queryData {@link PHI_RouteResolver::buildRequestPath()} メソッドを参照。
   * @param bool $absolute {@link PHI_RouteResolver::buildRequestPath()} メソッドを参照。
   * @param bool $secure {@link PHI_RouteResolver::buildRequestPath()} メソッドを参照。
   * @return string {@link PHI_RouteResolver::buildRequestPath()} メソッドを参照。
   */
  public function buildRequestPath($path, $queryData, $absolute = FALSE, $secure = NULL)
  {
    if (self::$_baseRouteName !== NULL) {
      if (is_array($path)) {
        if (!isset($path['route'])) {
          $path['route'] = self::$_baseRouteName;
        }

      } else if (ctype_upper(substr($path, 0, 1))) {
        $path = array('action' => $path, 'route' => self::$_baseRouteName);
      }
    }

    return $this->_router->buildRequestPath($path, $queryData, $absolute, $secure);
  }

  /**
   * ヘルパメソッドに渡された引数をヘルパが理解可能な配列形式に変換します。
   * <code>
   * $parameters = array('foo' => '100', 'bar' => '200');
   * $defaults = array('bar' => '100', 'baz' => '300');
   *
   * // array('foo' => '100', 'bar' => '200', 'baz' => '300')
   * PHI_Helper::constructParameters($parameters, $defaults);
   * </code>
   *
   * @param mixed $parameters 配列、または {@link PHI_HTMLEscapeDecorator} オブジェクトのインスタンス。
   * @param array $defaults データが持つデフォルト値。
   * @return array 配列形式のデータを返します。
   */
  protected static function constructParameters($parameters, $defaults = array())
  {
    $array = array();

    if (is_array($parameters)) {
      $array = $parameters;

    } else if ($parameters instanceof PHI_HTMLEscapeArrayDecorator) {
      $array = $parameters->getRaw();

    } else if ($parameters instanceof PHI_HTMLEscapeObjectDecorator) {
      $raw = $parameters->getRaw();

      if ($raw instanceof PHI_ParameterHolder) {
        $array = $raw->toArray();
      }
    }

    if (sizeof($defaults)) {
      $array = array_merge($defaults, $array);
    }

    return $array;
  }

  /**
   * HTML タグに追加する属性文字列を構築します。
   *
   * @param mixed $attributes タグに追加する属性。{@link PHI_HTMLHelper::link()} メソッドを参照。
   * @param bool $closingTag 閉じタグを付ける場合は TRUE、付けない場合は FALSE を指定。
   * @return string 生成した属性文字列を返します。
   */
  public static function buildTagAttribute($attributes, $closingTag = TRUE)
  {
    $attributes = self::constructParameters($attributes);
    $buffer = NULL;

    foreach ($attributes as $name => $value) {
      if (PHI_StringUtils::nullOrEmpty($name)) {
        $buffer .= $value . ' ';
      } else {
        $buffer .= sprintf('%s="%s" ', $name, $value);
      }
    }

    $buffer = trim($buffer);

    if (strlen($buffer)) {
      $buffer = ' ' . $buffer;
    }

    if ($closingTag) {
      $buffer .= ' /';
    }

    $buffer = PHI_StringUtils::escape($buffer, ENT_NOQUOTES);

    return $buffer;
  }
}
