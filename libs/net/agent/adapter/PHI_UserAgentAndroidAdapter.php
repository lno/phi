<?php
/**
 * Android 端末のためのユーザエージェントアダプタです。
 *
 * @package net.agent.adapter
 */
class PHI_UserAgentAndroidAdapter extends PHI_UserAgentAdapter
{
  /**
   * アダプタ名。
   */
  const ADAPTER_NAME = 'Android';

  /**
   * @return string
   */
  public function getAdapterName()
  {
    return self::ADAPTER_NAME;
  }

  /**
   * @see PHI_UserAgentAdapter::isAndroid()
   */
  public function isAndroid()
  {
    return TRUE;
  }

  /**
   * @see PHI_UserAgentAdapter::isSmartphone()
   */
  public function isSmartphone()
  {
    return TRUE;
  }

  /**
   * @see PHI_UserAgent::isValid()
   */
  public static function isValid($userAgent)
  {
    if (preg_match('/Android/', $userAgent) && strpos($userAgent, 'Mobile') !== FALSE) {
      return TRUE;
    }

    return FALSE;
  }
}
