<?php
/**
 * {@link PHI_View ビュー} を出力するための描画クラスです。
 *
 * <code>
 * $view = new PHI_View();
 * $view->setAttribtue('greeting', 'Hello World!');
 * $view->setTemplatePath($path);
 * $renderer = $view->getRenderer();
 * </code>
 *
 * 新しい描画クラスを作成する場合は、PHI_Renderer が提供する抽象メソッドを実装する必要があります。
 * <code>
 * // カスタム描画クラスの作成
 * class CustomRenderer extends PHI_Renderer
 * {
 *   public function getEngine()
 *   {}
 *
 *   public function render($data)
 *   {}
 *
 *   public function renderFile($path)
 *   {}
 * }
 *
 * // 描画クラスに CustomRenderer を使う
 * $view = new PHI_View(new CustomRenderer());
 * $view->setAttribute('greeting', 'Hello World!');
 * $view->execute();
 * </code>
 *
 * @package view.renderer
 */
abstract class PHI_Renderer extends PHI_Object
{
  /**
   * @var string
   */
  private $_cacheDirectory;

  /**
   * @var array
   */
  protected $_context = array();

  /**
   * コンストラクタ。
   */
  public function __construct()
  {
    $cacheDirectory = NULL;

    // テンプレート設置パスの取得
    if (PHI_BootLoader::isBootTypeWeb()) {
      $extension = PHI_Config::getApplication()->getString('view.extension');
      $route = PHI_FrontController::getInstance()->getRequest()->getRoute();

      if ($route) {
        $moduleName = $route->getModuleName();

        // キャッシュディレクトリの取得
        $cacheDirectory = sprintf('%s%scache%stemplates%scache%s%s',
          APP_ROOT_DIR,
          DIRECTORY_SEPARATOR,
          DIRECTORY_SEPARATOR,
          DIRECTORY_SEPARATOR,
          DIRECTORY_SEPARATOR,
          $moduleName);

      } else {
        // キャッシュディレクトリの取得
        $cacheDirectory = sprintf('%s%scache%stemplates%scache%sconsole',
          APP_ROOT_DIR,
          DIRECTORY_SEPARATOR,
          DIRECTORY_SEPARATOR,
          DIRECTORY_SEPARATOR,
          DIRECTORY_SEPARATOR);
      }
    }

    $this->_cacheDirectory = $cacheDirectory;
  }

  /**
   * 描画オブジェクトに {@link PHI_View ビュー} のコンテキスト情報を設定します。
   *
   * @param array &$context {@link PHI_View ビュー} から渡されるコンテキスト情報。
   */
  public function setContext(array &$context)
  {
    $this->_context = $context;
  }

  /**
   * キャッシュディレクトリを設定します。
   * このメソッドは描画エンジンがキャッシュ機能をサポートしている場合のみ有効です。
   *
   * @param string $cacheDirectory キャッシュディレクトリパス。
   *   APP_ROOT_DIR からの相対パス、または絶対パスが指定可能。
   */
  public function setCacheDirectory($cacheDirectory)
  {
    $this->_cacheDirectory = PHI_FileUtils::buildAbsolutePath($cacheDirectory);
  }

  /**
   * キャッシュディレクトリを取得します。
   * このメソッドはキャッシュディレクトリが存在しない場合にディレクトリの生成を試みます。
   *
   * @return string キャッシュディレクトリのパスを返します。
   */
  public function getCacheDirectory()
  {
    // キャッシュディレクトリがない場合は作成
    if (!is_dir($this->_cacheDirectory)) {
      PHI_FileUtils::createDirectory($this->_cacheDirectory, 0777);
    }

   return $this->_cacheDirectory;
  }

  /**
   * 描画エンジンオブジェクトを取得します。
   *
   * @return object 描画エンジンオブジェクトを取得します。
   */
  abstract public function getEngine();

  /**
   * データを描画します。
   *
   * @param string $data 描画対象のデータ。
   */
  abstract public function render($data);

  /**
   * ファイルの内容を描画します。
   * このメソッドは {@link PHI_View::execute()} メソッドからコールされます。
   *
   * @param string $path 描画対象のファイルパス。
   */
  abstract public function renderFile($path);
}
