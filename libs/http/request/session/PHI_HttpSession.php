<?php
/**
 * ユーザセッションを管理するための低レベル API を提供します。
 *
 * このクラスは 'session' コンポーネントとして DI コンテナに登録されているため、{@link PHI_DIContainer::getComponent()}、あるいは {@link PHI_WebApplication::getSession()} からインスタンスを取得することができます。
 *
 * @package http.request.session
 */
class PHI_HttpSession extends PHI_Object
{
  /**
   * セッション属性。
   * @var PHI_ParameterHolder
   */
  private $_config;

  /**
   * セッションが開始されているかどうか。
   * @var bool
   */
  private $_isActive = FALSE;

  /**
   * コンストラクタ。
   */
  public function __construct()
  {
    $this->_config = PHI_Config::getApplication()->get('session');

    if ($this->_config->getBoolean('autoStart')) {
      $this->activate();
    }

    register_shutdown_function(array($this, 'finalize'));
  }

  /**
   * ユーザオブジェクトを取得します。
   *
   * @return PHI_AuthorityUser ユーザオブジェクトを返します。
   */
  public function getUser()
  {
    return PHI_DIContainerFactory::getContainer()->getComponent('user');
  }

  /**
   * セッションを開始します。
   * このメソッドはフレームワークによってセッションが開始されたタイミングで内部的にコールされます。
   * <code>
   * // セッションが有効な状態にあるかどうかチェック
   * $session->isActive();
   *
   * // セッションを閉じる (通常はコールする必要はない)
   * $session->finalize();
   *
   * // 新しいセッションを開始する
   * $session->active();
   * </code>
   *
   * @return bool セッションの開始に成功した場合は TRUE、失敗した場合は FALSE を返します。
   */
  public function activate()
  {
    if ($this->_isActive) {
      return FALSE;
    }

    $config = $this->_config;

    // セッション維持方法の取得
    switch ($config->get('store')) {
      case 'transparent':
        ini_set('session.use_cookies', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.use_trans_sid', 0);

        ini_set('arg_separator.output', '&amp;');
        break;

      case 'cookie':
        ini_set('session.use_cookies', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.use_trans_sid', 0);
        ini_set('arg_separator.output', '&');
        break;
    }

    // 有効期限の設定
    $timeout = $config->getInt('timeout');

    if ($timeout > 0) {
      $lifetime = ini_get('session.gc_maxlifetime');

      if (PHI_StringUtils::nullOrEmpty($lifetime) || $lifetime < $timeout) {
        ini_set('session.gc_maxlifetime', $timeout);
      }
    }

    // セッション ID のランダム性を強化
    if (PHP_OS === 'Linux') {
      // '/dev/random' はロックする可能性があるため使用しない
      $file = '/proc/net/dev';
      $openDir = ini_get('open_basedir');

      if (empty($openDir) || preg_match('/^\/proc/', $openDir) || preg_match('/:\/proc/', $openDir)) {
        ini_set('session.entropy_file', $file);
        ini_set('session.entropy_length', 32);
      }
    }

    ini_set('session.entropy_length', 32);
    ini_set('session.hash_function', 'sha256');

    // セッション名の変更
    session_name($config->get('name'));

    // Cookie の制御
    $lifetime = $config->get('cookieLifetime');
    $path = $config->get('cookiePath');
    $domain = $config->get('cookieDomain');
    $secure = $config->getBoolean('cookieSecure');
    $httpOnly = $config->getBoolean('cookieHttpOnly');

    session_set_cookie_params($lifetime, $path, $domain, $secure, $httpOnly);

    // セッションハンドラの起動
    $handlerConfig = $config->get('handler');

    if ($handlerConfig) {
      $handlerClass = $handlerConfig->get('class');

      ini_set('session.save_handler', 'user');
      call_user_func_array(array($handlerClass, 'handler'), array($handlerConfig));
    }

    session_cache_limiter('none');

    // セッションの開始
    if (session_start()) {
      $this->_isActive = TRUE;

      return TRUE;
    }

    return FALSE;
  }

  /**
   * セッションが開始されているかチェックし、開始されていなければ例外をスローします。
   *
   * @return bool セッションが開始されている場合に TRUE を返します。
   * @throws RuntimeException セッションが開始されていない場合に発生。
   */
  private function checkActived()
  {
    if (!$this->_isActive) {
      $message = 'Session has not been started.';
      throw new RuntimeException($message);
    }

    return TRUE;
  }

  /**
   * 現在有効なセッション ID を取得します。
   *
   * @return string 現在有効なセッション ID を返します。
   * @throws RuntimeException セッションが開始していない場合に発生。
   */
  public function getId()
  {
    if ($this->checkActived()) {
      return session_id();
    }
  }

  /**
   * 現在有効なセッション名を取得します。
   *
   * @return string 現在有効なセッション名を返します。
   */
  public function getName()
  {
    return session_name();
  }

  /**
   * セッション ID を作り直します。
   * ユーザが既に保持しているデータは新しいセッションに引き継がれます。
   *
   * @param string $updateId 新しいセッション ID 。未指定の場合は自動生成される。
   * @return bool 更新に成功した場合は TRUE、失敗した場合は FALSE を返します。
   * @throws RuntimeException セッションが開始していない場合に発生。
   */
  public function updateId($updateId = NULL)
  {
    $result = FALSE;

    if ($this->checkActived()) {
      if ($updateId === NULL) {
        $result = session_regenerate_id(TRUE);

      } else {
        $data = $_SESSION;

        $this->clear();
        $this->finalize();

        session_id($updateId);
        $this->activate();

        $_SESSION = $data;
        $result = TRUE;
      }
    }

    return $result;
  }

  /**
   * セッションが開始されているかどうかチェックします。
   *
   * @return bool セッションが開始している場合は TRUE、開始していない場合は FALSE を返します。
   */
  public function isActive()
  {
    return $this->_isActive;
  }

  /**
   * 現在のセッションに関連付けられている全てのデータを破棄します。
   *
   * @return bool セッションの破棄に成功した場合は TRUE、失敗した場合は FALSE を返します。
   */
  public function clear()
  {
    if ($this->_isActive) {
      $_SESSION = array();
      $sessionName = session_name();

      $user = PHI_FrontController::getInstance()->getRequest()->getSession()->getUser();

      if (isset($_COOKIE[$sessionName])) {
        setcookie($sessionName, '', -1, '/');
      }

      return session_destroy();
    }

    return TRUE;
  }

  /**
   * セッションコンテキストに格納されているデータを取得します。
   *
   * @param string $namespace コンテキストの名前空間。
   * @return array セッションコンテキストに格納されているデータを返します。
   * @throws RuntimeException セッションが開始していない場合に発生。
   */
  public function &getContext($namespace = NULL)
  {
    if ($this->checkActived()) {
      if ($namespace === NULL) {
        return $_SESSION;

      } else if (isset($_SESSION[$namespace])) {
        return $_SESSION[$namespace];
      }

      $_SESSION[$namespace] = array();

      return $_SESSION[$namespace];
    }
  }

  /**
   * セッションデータを出力して終了します。
   */
  public function finalize()
  {
    try {
      if ($this->_isActive) {
        $user = PHI_FrontController::getInstance()->getRequest()->getSession()->getUser();
        $user->finalize();

        session_write_close();
        $this->_isActive = FALSE;
      }

    } catch (Exception $e) {
      PHI_ExceptionStackTraceDelegate::invoker($e);
      die();
    }
  }
}
