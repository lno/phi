<?php
require PHI_LIBS_DIR . '/net/agent/adapter/PHI_UserAgentAdapter.php';
require PHI_LIBS_DIR . '/net/agent/adapter/PHI_UserAgentAndroidAdapter.php';
require PHI_LIBS_DIR . '/net/agent/adapter/PHI_UserAgentAndroidTabletAdapter.php';
require PHI_LIBS_DIR . '/net/agent/adapter/PHI_UserAgentAUAdapter.php';
require PHI_LIBS_DIR . '/net/agent/adapter/PHI_UserAgentDefaultAdapter.php';
require PHI_LIBS_DIR . '/net/agent/adapter/PHI_UserAgentDoCoMoAdapter.php';
require PHI_LIBS_DIR . '/net/agent/adapter/PHI_UserAgentIPhoneAdapter.php';
require PHI_LIBS_DIR . '/net/agent/adapter/PHI_UserAgentIPadAdapter.php';
require PHI_LIBS_DIR . '/net/agent/adapter/PHI_UserAgentSoftBankAdapter.php';

/**
 * クライアントのブラウザ情報を識別するためのアダプタクラスを提供します。
 * PHI_UserAgent にはあらかじめいくつかのアダプタが登録されています。
 *   - {@link PHI_UserAgentDoCoMoAdapter}
 *   - {@link PHI_UserAgentAUAdapter}
 *   - {@link PHI_UserAgentSoftBankAdapter}
 *   - {@link PHI_UserAgentIPhoneAdapter}
 *   - {@link PHI_UserAgentIPadAdapter}
 *   - {@link PHI_UserAgentAndroidAdapter}
 *   - {@link PHI_UserAgentAndroidTabletAdapter}
 *   - {@link PHI_UserAgentDefaultAdapter}
 * 開発者はこれらに加え、新しいエージェントアダプタを登録することも可能です。
 * 詳しくは {@link addAdapter()} メソッドを参照して下さい。
 *
 * @package net.agent
 */
class PHI_UserAgent extends PHI_Object
{
  /**
   * アダプタリスト。
   * @var array
   */
  private $_adapters = array(
    'PHI_UserAgentDoCoMoAdapter',
    'PHI_UserAgentAUAdapter',
    'PHI_UserAgentSoftBankAdapter',
    'PHI_UserAgentIPhoneAdapter',
    'PHI_UserAgentIPadAdapter',
    'PHI_UserAgentAndroidAdapter',
    'PHI_UserAgentAndroidTabletAdapter'
  );

  /**
   * コンストラクタ。
   */
  private function __construct()
  {}

  /**
   * PHI_UserAgent のインスタンスを取得します。
   *
   * @return PHI_UserAgent PHI_UserAgent のインスタンスを返します。
   */
  public static function getInstance()
  {
    static $instance = NULL;

    if ($instance === NULL) {
      $instance = new PHI_UserAgent();
    }

    return $instance;
  }

  /**
   * ユーザエージェントアダプタを追加します。
   *
   * @param string $className {@link PHI_UserAgentAdapter} を実装したアダプタのクラス名。
   */
  public function addAdapter($className)
  {
    array_unshift($this->_adapters, $className);
  }

  /**
   * 登録されている全てのアダプタリストを取得します。
   *
   * @return array 登録されている全てのアダプタリストを返します。
   */
  public function getAdapters()
  {
    return $this->_adapters;
  }

  /**
   * ユーザエージェントのアダプタを取得します。
   *
   * @param string $userAgent ユーザエージェント文字列。
   * @return PHI_UserAgentAdapter PHI_UserAgentAdapter のインスタンスを返します。
   */
  public function getAdapter($userAgent)
  {
    $adapter = NULL;

    foreach ($this->_adapters as $className) {
      if (call_user_func(array($className, 'isValid'), $userAgent)) {
        $adapter = $className;
        break;
      }
    }

    if ($adapter === NULL) {
      $adapter = 'PHI_UserAgentDefaultAdapter';
    }

    $adapter = new $adapter($userAgent);
    $adapter->parse();

    return $adapter;
  }
}
