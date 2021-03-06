<?php
/**
 * HTML の作成を補助するヘルパメソッドを提供します。
 * このヘルパは、$html という変数名であらかじめテンプレートにインスタンスが割り当てられています。
 *
 * <code>
 * <?php echo $html->{method}; ?>
 * </code>
 *
 * global_helpers.yml の設定例:
 * <code>
 * html:
 *   # ヘルパクラス名。
 *   class: PHI_HTMLHelper
 *
 *   # {@link includeCSS()} メソッドが参照する基底 CSS パス。
 *   baseCSSPath: /assets/css
 *
 *   # {@link includeJS()} メソッドが参照する基底 JavaScript パス。
 *   baseJSPath: /assets/js
 *
 *   # {@link image()} メソッドが参照する基底イメージパス。
 *   baseImagePath: /assets/images
 *
 *   # 圧縮した CSS のディレクトリを参照する場合に TRUE を指定。{@link includeCSS()} の項を参照。
 *   cssMinify: FALSE
 *
 *   # 圧縮した JS のディレクトリを参照する場合に TRUE を指定。{@link includeJS()} の項を参照。
 *   jsMinify: FALSE
 * </code>
 * <i>その他に指定可能な属性は {@link PHI_Helper} クラスを参照。</i>
 *
 * @package view.helper
 */
class PHI_HTMLHelper extends PHI_Helper
{
  /**
   * @var array
   */
  protected static $_defaultValues = array(
    'baseCSSPath' => '/assets/css',
    'baseJSPath' => '/assets/js',
    'baseImagePath' => '/assets/images',
    'cssMinify' => FALSE,
    'jsMinify' => FALSE
  );

  /**
   * @var PHI_ActionMessages
   */
  private $_messages;

  /**
   * インクルードファイルの階層を管理。
   * @var array
   */
  private $_pathMapping = array();

  /**
   * テンプレートの拡張子。
   * @var string
   */
  private $_extension;

  /**
   * コンストラクタ。
   *
   * @see PHI_Helper::__construct()
   */
  public function __construct(PHI_View $currentView, array $config = array())
  {
    parent::__construct($currentView, $config);

    $this->_messages = $this->getMessages();
    $this->_extension = PHI_Config::getApplication()->getString('action.extension');
  }

  /**
   * スタイルシートのリンクタグを生成します。
   * このメソッドは、extra オプション 'ignoreCache' を TRUE に指定しない限り、ファイルが存在するかどうかのチェックは行いません。
   *
   * @param string $path CSS ファイルのパスを指定。'webroot/assets/css' からの相対パス、または絶対パス ('http://～'、あるいは '/' から始まるパス) が有効。
   * @param mixed $attributes リンクタグに追加する属性。{@link PHI_HTMLHelper::link()} メソッドを参照。
   * @param mixed $extra オプション属性。{@link buildAssetPath()} メソッドを参照。
   * @return string 生成したスタイルシートのリンクタグを返します。
   *   'html.cssMinify' 属性が TRUE の場合、CSS のコードを圧縮したファイル (ディレクトリ) を参照するようになります。
   *   圧縮ファイルのパスは、'/assets/css/min/example.css' のような形式になります。
   */
  public function includeCSS($path, $attributes = array(), $extra = array())
  {
    $path = $this->buildAssetPath($path, 'css', $extra);

    $defaults = array();
    $defaults['rel'] = 'stylesheet';
    $defaults['type'] = 'text/css';
    $defaults['href'] = $path;

    $parameters = self::constructParameters($attributes, $defaults);
    $attributes = self::buildTagAttribute($parameters);

    $tag = sprintf("<link%s>\n", $attributes);

    return $tag;
  }

  /**
   * JavaScript のリンクタグを生成します。
   * このメソッドは、extra オプション 'ignoreCache' を TRUE に指定しない限り、ファイルが存在するかどうかのチェックは行いません。
   *
   * @param string $path JS ファイルのパスを指定。'webroot/assets/js' からの相対パス、または絶対パス ('http://～'、あるいは '/' から始まるパス) が有効。
   * @param mixed $attributes リンクタグに追加する属性。{@link PHI_HTMLHelper::link()} メソッドを参照。
   * @param mixed $extra オプション属性。{@link buildAssetPath()} メソッドを参照。
   * @return string 生成した JavaScript のリンクタグを返します。
   *   'html.jsMinify' 属性が TRUE の場合、JavaScript のコードを圧縮したファイル (ディレクトリ) を参照するようになります。
   *   圧縮ファイルのパスは、'/assets/js/min/example.js' のような形式になります。
   */
  public function includeJS($path, $attributes = array(), $extra = array())
  {
    $path = $this->buildAssetPath($path, 'js', $extra);

    $defaults = array();
    $defaults['type'] = 'text/javascript';
    $defaults['src'] = $path;

    $parameters = self::constructParameters($attributes, $defaults);
    $attributes = self::buildTagAttribute($parameters, FALSE);
    $tag = sprintf("<script%s></script>\n", $attributes);

    return $tag;
  }

  /**
   * 指定されたパスを元に、Web から参照可能なリクエストパスを生成します。
   *
   * @param string $path 変換対象のファイルパス。既定パスは type 値によります。
   *   例えば CSS ファイル 'base.css' を指定した場合に生成されるリクエストパスは '/assets/css/base.css' となります。
   * @param string $type path のファイルタイプ。指定可能な値は次の通り。
   *   - css: 'baseCSSPath' からの相対パスを生成。
   *   - js: 'baseJSPath' からの相対パスを生成。
   *   - image: 'baseImagePath' からの相対パスを生成。
   * @param array $extra タグの出力オプション。
   *   - absolute: 相対パスを絶対パスに変換します。
   *   - query: path に追加するクエリパラメータ。連想配列形式で指定。
   *   - ignoreCache: ファイルの最終更新時刻をクエリに追加する場合は TRUE を指定。
   *       ファイルがローカルキャッシュされる問題を回避したい場合に有効です。
   *       生成されるパスは '/assets/css/example.css?1315552397' のような形式になります。
   */
  public function buildAssetPath($path, $type, $extra)
  {
    $ignoreCache = PHI_ArrayUtils::find($extra, 'ignoreCache', FALSE);
    $isLocalPath = FALSE;
    $isMinify = FALSE;
    $webrootPath = PHI_AppPathManager::getInstance()->getWebrootPath();

    if ($type === 'css') {
      $isMinify = $this->_config->getBoolean('cssMinify');
    } else if ($type === 'js') {
      $isMinify = $this->_config->getBoolean('jsMinify');
    }

    if (PHI_URIUtils::isAbsoluteURI($path)) {
      if (PHI_URIUtils::isSameDomain($path)) {
        if ($isMinify) {
          $path = $this->buildMinifyPath($path);
        }

        $parse = parse_url($path);

        $relativePath = $path;
        $absolutePath = $webrootPath . $parse['path'];

        $isLocalPath = TRUE;

      } else {
        $relativePath = $path;
      }

    } else {
      if ($isMinify) {
        $path = $this->buildMinifyPath($path);
      }

      if (substr($path, 0, 1) === '/' || substr($path, 0, 2) === '..') {
        $relativePath = $path;

      } else {
        switch ($type) {
          case 'css':
            $basePath = $this->_config->getString('baseCSSPath');
            break;

          case 'js':
            $basePath = $this->_config->getString('baseJSPath');
            break;

          case 'image':
            $basePath = $this->_config->getString('baseImagePath');
            break;
        }

        $relativePath = $basePath . '/' . $path;
      }

      $absolutePath = $webrootPath . $relativePath;
      $absolute = PHI_ArrayUtils::find($extra, 'absolute', FALSE);

      if ($absolute) {
        $request = $this->getRequest();
        $relativePath = sprintf('%s://%s%s',
          $request->getScheme(),
          $request->getHost(),
          $relativePath);
      }

      $isLocalPath = TRUE;
    }

    $queryData = PHI_ArrayUtils::find($extra, 'query', array());
    $separator = NULL;

    if (sizeof($queryData)) {
      if (strpos($path, '?') === FALSE) {
        $separator = '?';
      } else {
        $separator = '&';
      }

      $relativePath = $relativePath . $separator . http_build_query($queryData, '', '&');
    }

    if ($ignoreCache && $isLocalPath && is_file($absolutePath)) {
      $status = stat($absolutePath);

      if ($separator === NULL) {
        $separator = '?';
      } else {
        $separator = '&';
      }

      $relativePath = $relativePath . $separator . $status[9];
    }

    return $relativePath;
  }

  /**
   * @param $path
   * @return string
   */
  private function buildMinifyPath($path)
  {
    $info = pathinfo($path);

    if ($info['dirname'] === '.') {
      $path = 'min/' . $info['basename'];
    } else {
      $path = $info['dirname'] . '/min/' . $info['basename'];
    }

    return $path;
  }

  /**
   * {@link link()} メソッドが生成するパスを絶対パスとして生成するかどうか設定の状態をチェックします。
   *
   * @return bool このメソッドは現在のところ必ず FALSE を返します。
   *   {@link PHI_HTMLHelper} を継承した {@link PHI_MixiMobileAppHTMLHelper::isAbsolutePath()} は TRUE を返します。
   */
  public function isAbsolutePath()
  {
    return FALSE;
  }

  /**
   * 文字列 string に含まれる search をハイライト表示します。
   *
   * @param string $string 対象文字列。
   * @param string $search ハイライト化する文字列。
   * @param string $markup マッチした文字列を囲うタグ。'\1' には search が格納されます。
   */
  public function highlight($string, $search, $markup = '<span class="highlight">\1</span>')
  {
    $regexp = '/(' . $search . ')/';
    $string = preg_replace($regexp, $markup, $string);

    return $string;
  }

  /**
   * {@link PHI_ActionMessages::hasMessage()} メソッドのエイリアスです。
   */
  public function hasMessage($messageId = NULL)
  {
    return $this->_messages->hasMessage($messageId);
  }

  /**
   * {@link PHI_ActionMessages メッセージオブジェクト} からメッセージ ID に紐付くメッセージを取得します。
   *
   * @param string $messageId メッセージ ID。
   * @param mixed $attributes タグに追加する属性。{@link PHI_HTMLHelper::link()} メソッドを参照。
   * @return string メッセージを含むタグを返します。メッセージ ID に紐付くメッセージが見つからない場合は NULL を返します。
   */
  public function message($messageId, $attributes = array('class' => 'success'))
  {
    $messages = $this->_messages->getList();

    if (isset($messages[$messageId])) {
      return $this->buildMessage($messages[$messageId], $attributes);
    }

    return NULL;
  }

  /**
   * {@link PHI_ActionMessages::add()} メソッドで追加された全てのメッセージを HTML タグを含む形式で取得します。
   * このメソッドはメッセージが複数含まれる場合にリスト形式の HTML を返します。
   *
   * @param mixed $attributes リストタグに追加する属性。{@link PHI_HTMLHelper::link()} メソッドを参照。
   * @return string メッセージを HTML のリスト形式で返します。メッセージが未登録の場合は NULL を返します。
   */
  public function messages($attributes = array('class' => 'success'))
  {
    if ($this->_messages->hasMessage()) {
      return $this->buildMessageTag($this->_messages->getList(), $attributes);
    }

    return NULL;
  }

  /**
   * {@link PHI_ActionMessages::hasError()} メソッドのエイリアスです。
   */
  public function hasError($messageId = NULL)
  {
    return $this->_messages->hasError($messageId);
  }

  /**
   * {@link PHI_ActionMessages メッセージオブジェクト} からエラーメッセージ ID に紐付くエラーメッセージを取得します。
   *
   * @param string $messageId エラーメッセージ ID。
   * @param mixed $attributes タグに追加する属性。{@link PHI_HTMLHelper::link()} メソッドを参照。
   * @return string エラーメッセージを含むタグを返します。エラーメッセージ ID に紐付くメッセージが見つからない場合は NULL を返します。
   */
  public function error($messageId, $attributes = array('class' => 'error'))
  {
    $messages = $this->_messages->getErrorList(PHI_ActionMessages::ERROR_TYPE_DEFAULT);

    if (isset($messages[$messageId])) {
      return $this->buildMessage($messages[$messageId], $attributes);
    }

    return NULL;
  }

  /**
   * {@link PHI_ActionMessages::addError()} メソッドで追加された全てのエラーメッセージを HTML タグを含む形式で取得します。
   * このメソッドはメッセージが複数含まれる場合にリスト形式の HTML を返します。
   *
   * @param bool $fieldError {@link PHI_ActionMessages::addFieldError()} メソッドで追加されたフィールドエラーメッセージを同時に出力する場合は TRUE を指定。
   * @param mixed $attributes リストタグに追加する属性。{@link PHI_HTMLHelper::link()} メソッドを参照。
   * @return string エラーメッセージを HTML のリスト形式で返します。エラーが未登録の場合は NULL を返します。
   */
  public function errors($fieldError = TRUE, $attributes = array('class' => 'error'))
  {
    $errorList = array();

    // アクションメッセージのみ出力
    if (!$fieldError) {
      $errorList = $this->_messages->getErrorList(PHI_ActionMessages::ERROR_TYPE_DEFAULT);

    // 全てのエラーを出力
    } else {
      $errorList = $this->_messages->getErrorList();
    }

    if (sizeof($errorList)) {
      return $this->buildMessageTag($errorList, $attributes);
    }

    return NULL;
  }

  /**
   * @param string $message
   * @param array $attributes
   * @return string
   */
  private function buildMessage($message, $attributes)
  {
    $buffer = sprintf("<span%s>%s</span>\n",
      self::buildTagAttribute($attributes, FALSE),
      $message);

    return $buffer;
  }

  /**
   * @param array $messages
   * @param array $attributes
   * @return string
   */
  private function buildMessageTag($messages, $attributes)
  {
    $buffer = NULL;
    $attributes = self::buildTagAttribute($attributes, FALSE);

    if (sizeof($messages) > 1) {
      $buffer = sprintf("<div%s>\n<ul>\n", $attributes);

      foreach ($messages as $message) {
        $buffer .= sprintf("<li>%s</li>\n", PHI_StringUtils::escape($message));
      }

      $buffer .= "</ul>\n</div>\n";

    } else {
      $buffer = sprintf("<div %s>\n%s</div>\n",
        $attributes,
        PHI_StringUtils::escape(current($messages)));
    }

    return $buffer;
  }

  /**
   * テンプレートファイルを読み込みます。
   *
   * <code>
   * # modules/{module}/templates/includes/header.php を読み込む。
   * #   - 各種ヘルパは子テンプレートでも使用可能
   * #   - 現在のテンプレートディレクトリからの相対パスでファイルを指定
   * #   - '/' から始まるパスはテンプレート基底ディレクトリからの絶対パスと見なされる
   * #   - 拡張子はオプション。未指定時は application.yml に定義された 'view.extension' 属性が参照される
   * $html->includeTemplate('includes/header');
   *
   * # header.php を読み込む際に $foo、$bar 変数を宣言。
   * $html->includeTemplate('includes/header', array('foo' => 100, 'bar' => 200));
   *
   * # {APP_ROOT_DIR}/templates/html/global_footer.php を読み込む。
   * $html->includeTemplate('@templates/html/global_footer');
   * </code>
   *
   * @param string $path 参照するテンプレートのパスを指定。
   * @param mixed $attributes テンプレートに渡す変数を連想配列形式 (変数名、変数値) で指定。変数の値は自動的に HTML エスケープされる。
   * @param mixed $unescapeAttributes HTML エスケープを必要としない変数のリスト。
   */
  public function includeTemplate($path, array $attributes = array(), array $unescapeAttributes = array())
  {
    $size = sizeof($this->_pathMapping);
    $isAbsolutePath = FALSE;

    if (substr($path, 0, 1) === '/') {
      $isAbsolutePath = TRUE;
    }

    if ($isAbsolutePath) {
      $route = $this->getRequest()->getRoute();
      $basePath = $this->getAppPathManager()->getModuleTemplatesPath($route->getModuleName());
      $templatesDirectory = dirname($basePath . $path);
      $path = basename($path);

    } else {
      if ($size == 0) {
        $templatesDirectory = dirname($this->_currentView->getTemplatePath());
      } else {
        $templatesDirectory = $this->_pathMapping[$size - 1];
      }
    }

    // 親テンプレートからの相対パス上にファイルが存在するかチェック
    $extension = PHI_Config::getApplication()->getString('view.extension');
    $path = PHI_AppPathManager::buildAbsolutePath($templatesDirectory, $path, $extension);

    $load = function(&$pathMapping, $path, $variables)
    {
      // ファイルが存在する場合は読み込む
      if (is_file($path)) {
        extract($variables);

        array_push($pathMapping, dirname($path));
        require $path;

      } else {
        $message = sprintf('Template file does not exist. [%s]', $path);
        throw new PHI_IOException($message);
      }
    };

    // メソッドに渡された変数リストをエスケープ
    $helpers = $this->_currentView->getHelpers();
    $variables = $this->_currentView->getAttributes();

    foreach ($helpers as $name => $value) {
      $variables[$name] = $value;
    }

    foreach ($attributes as $name => $value) {
      $variables[$name] = PHI_StringUtils::escape($value);
    }

    // HTML エスケープを必要としない変数のリストをマージする
    if (sizeof($unescapeAttributes)) {
      $variables = PHI_ArrayUtils::merge($variables, $unescapeAttributes);
    }

    $load($this->_pathMapping, $path, $variables);

    array_pop($this->_pathMapping);
  }

  /**
   * リンクタグを生成します。
   *
   * @param string $label リンクのラベル。未指定の場合は path がラベルとして扱われます。
   * @param mixed $path リンク先のパス。指定可能なパスの書式は {@link PHI_RouteResolver::buildRequestPath()} メソッドを参照。
   * @param mixed $attributes タグに追加する属性。
   *   <code>
   *   // タグに 'class'、'title' 属性を追加
   *   $attributes = array('class' => 'field', 'title' => 'Hello');
   *   </code>
   * @param array $extra タグの出力オプション。
   *   - escape: label に指定された文字列をエスケープするかどうか。規定値は TRUE。
   *   - absolute: 相対パスを絶対パスに変換します。
   *   - secure: URI スキームの指定。詳しくは {@link PHI_RouteResolver::buildRequestPath()} を参照。既定値は NULL。
   *       (secure オプション指定時は absolute 属性は TRUE と見なされる)
   *   - query: パスに追加するクエリパラメータを連想配列形式で指定。
   * @return string 生成したリンクタグを返します。
   * @see PHI_RouteResolver::buildRequestPath()
   */
  public function link($label = NULL, $path = NULL, $attributes = array(), $extra = array())
  {
    $extra = self::constructParameters($extra);
    $queryData = PHI_ArrayUtils::find($extra, 'query', array());
    $secure = PHI_ArrayUtils::find($extra, 'secure');

    if ($secure === NULL) {
      $absolute = PHI_ArrayUtils::find($extra, 'absolute', FALSE);
    } else {
      $absolute = TRUE;
    }

    $path = $this->buildRequestPath($path, $queryData, $absolute, $secure);
    $buffer = $this->baseLink($label, $path, $attributes, $extra);

    return $buffer;
  }

  /**
   * @param string $label
   * @param string $path
   * @param array $attributes
   * @param array $extra
   * @return string
   */
  protected function baseLink($label, $path, $attributes, $extra)
  {
    $extra = self::constructParameters($extra);

    if ($label === NULL) {
      $label = $path;
    }

    if (PHI_ArrayUtils::find($extra, 'escape', TRUE)) {
      $label = PHI_StringUtils::escape($label);
    }

    $defaults = array('href' => $path);
    $attributes = self::constructParameters($attributes, $defaults);

    // $path は buildTagAttribute() 内で HTML エスケープ処理が行われる
    $buffer = sprintf('<a%s>%s</a>',
      self::buildTagAttribute($attributes, FALSE),
      $label);

    return $buffer;
  }

  /**
   * 文字列中に含まれる URI や E-Mail アドレスにアンカーを追加します。
   * <i>このメソッドは、HTML ソースに対し実行するべきではありません。HTML タグに含まれるリンクに重ねてアンカーが張られる可能性があります。</i>
   *
   * @param string $string 対象となる文字列。
   * @param mixed $attributes タグに追加する属性。{@link PHI_HTMLHelper::link()} メソッドを参照。
   * @param bool $email TRUE を指定した場合、E-Mail アドレスもリンク対象とする。
   * @param string $cushionPath クッションパス。
   *   文字列中に外部の URI が含まれる場合、直接アンカーを張るのではなく、クッションページに遷移させることが可能。
   *   クッションページには遷移先の URI が '?uri={URI}' 形式で付加される。
   *   指定可能なパスの書式は {@link PHI_RouteResolver::buildRequestPath()} メソッドを参照。
   * @return string 変換後の文字列を返します。
   */
  public function autoLink($string, $attributes = array(), $email = FALSE, $cushionPath = NULL)
  {
    $attributes = self::buildTagAttribute($attributes, FALSE);
    $uriRegexp = '/' . substr(PHI_URLValidator::URL_QUERY_PATTERN, 2, -2) . '/';

    if ($cushionPath !== NULL) {
      // PHP 5.3 では $this を渡すことはできない (PHP 5.4 から可能)
      $helper = $this;

      $string = preg_replace_callback($uriRegexp,
        function($matches) use($attributes, $cushionPath, $helper)
        {
          if (PHI_URIUtils::isSameDomain($matches[1])) {
            $path = PHI_StringUtils::escape($matches[1]);
            $value = sprintf('<a href="%s"%s>%s</a>', $path, $attributes, $path);

          } else {
            $queryData = array('uri' => $matches[1]);
            $basePath = $helper->buildRequestPath($cushionPath, $queryData);
            $value = sprintf('<a href="%s"%s>%s</a>', PHI_StringUtils::escape($basePath), $attributes, PHI_StringUtils::escape($matches[1]));
          }

          return $value;
        },
        $string);

    } else {
      $string = preg_replace_callback($uriRegexp,
        function($matches) use($attributes)
        {
          $path = PHI_StringUtils::escape($matches[1]);
          $value = sprintf('<a href="%s"%s>%s</a>', $path, $attributes, $path);

          return $value;
        },
        $string);
    }

    if ($email) {
      $mailRegexp = '/' . substr(PHI_EMailValidator::EMAIL_TRANSITIONAL_PATTERN, 2, -2) . '/';
      $string = preg_replace($mailRegexp,
        '<a href="mailto:\\1">\\1</a>',
        $string);
    }

    return $string;
  }

  /**
   * イメージタグを生成します。
   * このメソッドは、extra オプション 'ignoreCache' を TRUE に指定しない限り、ファイルが存在するかどうかのチェックは行いません。
   *
   * @param mixed $path 画像のパス。指定可能なパスの書式は {@link PHI_RouteResolver::buildRequestPath()} メソッドを参照。
   * @param mixed $attributes タグに追加する属性。{@link PHI_HTMLHelper::link()} メソッドを参照。
   * @param mixed $extra オプション属性。{@link buildAssetPath()} メソッドを参照。
   * @return string 生成したイメージタグを返します。
   */
  public function image($path, $attributes = array(), $extra = array())
  {
    $extra = self::constructParameters($extra);

    $absolute = PHI_ArrayUtils::find($extra, 'absolute', FALSE);
    $queryData = PHI_ArrayUtils::find($extra, 'query', array());
    $path = $this->buildRequestPath($path, $queryData, $absolute);

    $defaults = array();
    $defaults['src'] = $this->buildAssetPath($path, 'image', $extra);

    $attributes = self::constructParameters($attributes, $defaults);
    $buffer = self::buildTagAttribute($attributes);

    return sprintf("<img%s>", $buffer);
  }

  /**
   * 配列 array を元に順序のないリストタグを生成します。
   *
   * @param array $array リストタグの元となる配列。連想配列を指定した場合はネストされたリストが生成される。
   * @param mixed $attributes タグに追加する属性。{@link PHI_HTMLHelper::link()} メソッドを参照。
   * @param array $extra タグの出力オプション。
   *   - separator: ネストされたリストを生成する際にキーと値を区切る文字列。既定値は '='。
   * @return array 生成したリストタグを返します。
   */
  public function ul($array, $attributes = array(), $extra = array())
  {
    $extra = self::constructParameters($extra);
    $extra['_type'] = 'ul';

    return $this->buildList($array, $attributes, $extra);
  }

  /**
   * 配列 array を元に順序付きのリストタグを生成します。
   *
   * @see PHI_HTMLHelper::ul()
   */
  public function ol($array, $attributes = array(), $extra = array())
  {
    $extra = self::constructParameters($extra);
    $extra['_type'] = 'ol';

    return $this->buildList($array, $attributes, $extra);
  }

  /**
   * @param array $array
   * @param array $attributes
   * @param array $extra
   * @return string
   */
  private function buildList($array, $attributes, $extra)
  {
    $buffer = NULL;
    $array = self::constructParameters($array);

    if (sizeof($array)) {
      $extra['separator'] = PHI_ArrayUtils::find($extra, 'separator', '=');

      $buffer = self::buildTagAttribute($attributes, FALSE);
      $buffer = sprintf("<%s%s>\n",
        $extra['_type'],
        $buffer);
      $this->_buildList($array, $buffer, $extra);

      $buffer .= sprintf("</%s>\n", $extra['_type']);
    }

    return $buffer;
  }

  /**
   * @param array $array
   * @param string &$buffer
   * @param array $extra
   */
  private function _buildList($array, &$buffer, $extra)
  {
    $isHash = PHI_ArrayUtils::isAssoc($array);

    foreach ($array as $name => $value) {
      if (is_array($value)) {
        if (sizeof($value)) {
          $buffer .= sprintf("<li>%s\n<%s>\n", $name, $extra['_type']);
          $this->_buildList($value, $buffer, $extra);
          $buffer .= sprintf("</%s>\n</li>\n",  $extra['_type']);

        } else {
          $buffer .= sprintf("<li>%s</li>\n", $name);
        }

      } else {
        if ($isHash) {
          $buffer .= sprintf("<li>%s%s%s</li>\n",
                             $name,
                             $extra['separator'],
                             $value);
        } else {
          $buffer .= sprintf("<li>%s</li>\n", $value);
        }
      }
    }
  }
}
