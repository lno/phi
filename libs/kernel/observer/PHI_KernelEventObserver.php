<?php
/**
 * イベントリスナを制御します。
 *
 * @package kernel.observer
 */
class PHI_KernelEventObserver extends PHI_Object
{
  /**
   * @var PHI_ParameterHolder
   */
  private $_config;

  /**
   * @var string
   */
  private $_listener;

  /**
   * @var array
   */
  private $_listeners = array();

  /**
   * コンストラクタ。
   */
  public function __construct()
  {
    $this->_config = PHI_Config::getApplication();

    if (PHI_BootLoader::isBootTypeWeb()) {
      $this->_listener = new PHI_WebApplicationEventListener();
    } else {
      $this->_listener = new PHI_ConsoleApplicationEventListener();
    }
  }

  /**
   * オブザーバを初期化します。
   */
  public function Initialize()
  {
    $listeners = PHI_Config::getApplication()->get('observer.listeners');

    if ($listeners) {
      foreach ($listeners as $listenerId => $attributes) {
        $this->addEventListener($listenerId, $attributes);
      }
    }

    register_shutdown_function(array($this, 'dispatchEvent'), 'preShutdown');
  }

  /**
   * オブザーバにイベントリスナを追加します。
   * イベントリスナの登録に成功した場合は TRUE、失敗 (現在の起動モードと {@link PHI_ApplicationEventListener::getBootMode() リスナの起動モード} が異なる場合に FALSE を返します。
   *
   * @param string $listenerId イベントリスナ ID。
   * @param PHI_ParameterHolder $holder イベントリスナ属性。
   * @return bool
   */
  public function addEventListener($listenerId, PHI_ParameterHolder $holder)
  {
    $result = FALSE;

    $className = $holder->get('class');
    $instance = new $className($holder);

    $arrowBootMode = $instance->getBootMode();
    $currentBootMode = PHI_BootLoader::getBootMode();

    if ($arrowBootMode & $currentBootMode) {
      $this->_listeners[$listenerId] = $instance;
      $this->dispatchEvent('preProcess');

      $result = TRUE;
    }

    return $result;
  }

  /**
   * 指定したイベントリスナがオブザーバに登録されているかチェックします。
   *
   * @param string $listenerId チェック対象のイベントリスナ ID。
   * @return bool イベントリスナが登録されている場合は TRUE、登録されていない場合は FALSE を返します。
   */
  public function hasEventListener($listenerId)
  {
    return isset($this->_listeners[$listenerId]);
  }

  /**
   * オブザーバに登録されている全てのイベントリスナを取得します。
   *
   * @return array オブザーバに登録されている全てのイベントリスナを返します。
   */
  public function getEventListeners()
  {
    return $this->_listeners;
  }

  /**
   * オブザーバに登録されているイベントリスナを削除します。
   *
   * @param string $listenerId 削除対象のイベントリスナ ID。
   * @return bool イベントリスナの削除に成功した場合は TRUE、失敗した場合は FALSE を返します。
   */
  public function removeEventListener($listenerId)
  {
    if (isset($this->_listeners[$listenerId])) {
      unset($this->_listeners[$listenerId]);

      return TRUE;
    }

    return FALSE;
  }

  /**
   * オブザーバに登録されているイベントを実行します。
   *
   * @param string $event イベントリスナに登録されているイベント (メソッド) 名。
   * @param array &$arguments イベントリスナに渡す引数のリスト。
   * @throws RuntimeException リスナーにメソッドが定義されていない、またはイベントリスナが {@link PHI_ApplicationEventListener} を継承していない場合に発生。
   */
  public function dispatchEvent($event, array &$arguments = array())
  {
    $catchEvent = FALSE;

    foreach ($this->_listeners as $listenerId => $instance) {
      if ($instance instanceof PHI_ApplicationEventListener) {
        $events = (array) $instance->getListenEvents();

        if (in_array($event, $events)) {
          if (method_exists($instance, $event)) {
            $catchEvent = TRUE;
            call_user_func_array(array($instance, $event), $arguments);

          } else {
            $message = sprintf('Method is not defined. [%s::%s()]', get_class($instance), $event);
            throw new RuntimeException($message);
          }
        }

      } else {
        $message = sprintf('Doesn\'t inherit PHI_ApplicationEventListener. [%s]', get_class($instance));
        throw new RuntimeException($message);
      }
    }

    if (!$catchEvent && method_exists($this->_listener, $event)) {
      call_user_func_array(array($this->_listener, $event), $arguments);
    }
  }
}
