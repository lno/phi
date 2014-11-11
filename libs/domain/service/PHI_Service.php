<?php
/**
 * サービスの抽象クラスです。
 * 全てのビジネスロジックは PHI_Service を継承したサービスクラス内で実装します。
 * <strong>サービスはシステムのユースケース単位で作成することを推奨します。</strong>
 *
 * 開発者が作成するのサービスは、クラス名のサフィックスとして 'Service' を付ける必要があります。
 * 例えば Greeting サービスであれば、クラス名は GreetingService となります。
 * 作成したサービスクラスは {APP_ROOT_DIR}/libs/service 下に配置して下さい。
 *
 * <code>
 * class GreetingService extends PHI_Service
 * {
 *   public function echo()
 *   {
 *     return 'Hello World!';
 *   }
 * }
 * </code>
 *
 * サービスのインスタンスは {@link PHI_ServiceFactory} から取得することができます。
 *
 * <code>
 * $greeting = PHI_ServiceFactory::get('Greeting');
 *
 * // {@link PHI_Object::getService()} を使った参照
 * $greeting = $this->getService('Greeting');
 * $greeting->echo();
 * </code>
 *
 * @package domain.service
 */
abstract class PHI_Service extends PHI_Object
{
  /**
   * コンストラクタ。
   * このメソッドは処理の最後に {@link initialize()} をコールします。
   */
  public function __construct()
  {
    $this->initialize();
  }

  /**
   * サービスの初期化を行うメソッドです。
   * {@link PHI_ServiceFactory::load()} メソッドでクラスをロードした場合、このメソッドは実行されない点に注意して下さい。
   */
  public function initialize()
  {}
}
