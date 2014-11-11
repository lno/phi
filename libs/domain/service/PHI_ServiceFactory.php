<?php
/**
 * {@link PHI_Service サービスクラス} のインスタンスを提供します。
 * このクラスを利用する場合、全てのサービスは {APP_ROOT_DIR}/libs/service 下に配置する必要があります。
 *
 * @package domain.service
 */
class PHI_ServiceFactory extends PHI_Object
{
  /**
   * サービスクラスのインスタンスを取得します。
   * 全てのサービスクラスは {@link PHI_Service} を継承した実装が必要です。
   *
   * @param string $serviceName 取得するサービス名。GreetingService を参照する場合は 'Greeting' を指定します。
   * @return object サービスのインスタンスを返します。
   */
  public static function get($serviceName)
  {
    static $instances = array();

    if (!isset($instances[$serviceName])) {
      $className = $serviceName . 'Service';
      $instances[$serviceName] = new $className;
    }

    return $instances[$serviceName];
  }
}
