<?php
/**
 * イベントリスナのための抽象クラスです。
 * Web アプリケーション用カスタムイベントリスナを作成する場合は、{@link PHI_WebApplicationEventListener}、コンソールアプリケーション用カスタムイベントリスナを作成する場合は {@link PHI_ConsoleApplicationEventListener} を継承すると良いでしょう。
 * <code>
 * class CustomEventListener extends PHI_WebApplicationEventListener
 * {
 *   // 'preProcess' イベントを {@link PHI_KernelEventObserver オブザーバ} に通知
 *   public function {@link getListenEvents}()
 *   {
 *     return array('preProcess');
 *   }
 * }
 * </code>
 *
 * @package kernel.observer.listener
 */
abstract class PHI_ApplicationEventListener extends PHI_Object
{
  /**
   * {@link PHI_KernelEventObserver オブザーバ} に通知するイベントリストを取得します。
   * 例えば '{@link PHI_KernelEventObserver::preOutput() preOutput}') をイベントとしてオブザーバに追加したい場合は、array('preOutput') を返すようにします。
   *
   * @return array オブザーバに通知するイベントリストを返します。
   */
  abstract public function getListenEvents();

  /**
   * リスナの実行を許可する起動モードを取得します。
   *
   * @return array 起動モード定数 ({@link PHI_BootLoader::BOOT_MODE_WEB}、あるいは {@link PHI_BootLoader::BOOT_MODE_CONSOLE)} を返します。
   *   Web とコンソール両方を許可する場合は、PHI_BootLoader::BOOT_MODE_WEB|PHI_BootLoader::BOOT_MODE_CONSOLE を返します。
   */
  abstract public function getBootMode();

  /**
   * イベントリスナのインスタンスが生成された直後に (アプリケーションが {@link PHI_BootLoader ブートローダ} によって初期化されるタイミングで) 起動します。
   */
  public function preProcess()
  {}

  /**
   * フレームワークの処理が正常に終了する時点で起動します。
   */
  public function postProcess()
  {}

  /**
   * プログラムがシャットダウンする直前 ({@link postProcess() よりも後) に起動します。
   * プログラム内で {@link http://php.net/manual/function.exit.php exit} を宣言した場合も実行される点に注意して下さい。
   */
  public function preShutdown()
  {}
}
