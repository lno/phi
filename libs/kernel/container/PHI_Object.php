<?php
/**
 * フレームワークのコンテキスト機能を提供します。
 *
 * @package kernel.container
 */
abstract class PHI_Object
{
  /**
   * オブジェクトを文字列として取得します。
   *
   * @return string オブジェクトを文字列データとして返します。
   */
  public function __toString()
  {
    return PHI_CommonUtils::convertVariableToString($this);
  }

  /**
   * サービスのインスタンスを取得します。
   * 詳細については {@link PHI_ServiceFactory::get()} や {@link PHI_Service} クラスを参照して下さい。
   *
   * @param string $serviceName 取得するサービス名。
   * @return PHI_Service サービスのインスタンスを返します。
   */
  public function getService($serviceName)
  {
    return PHI_ServiceFactory::get($serviceName);
  }

  /**
   * PHI_AppPathManager のインスタンスを取得します。
   *
   * @return PHI_AppPathManager PHI_AppPathManager のインスタンスを返します。
   */
  public function getAppPathManager()
  {
    return PHI_AppPathManager::getInstance();
  }

  /**
   * フレームワークのレジストリにアクセスします。
   *
   * @return PHI_ParameterHolder PHI_ParameterHolder のインスタンスを返します。
   */
  public static function getRegistry()
  {
    static $instance;

    if ($instance === NULL) {
      $instance = new PHI_ParameterHolder();
    }

    return $instance;
  }
}
