<?php
/**
 * Android タブレット端末のためのユーザエージェントアダプタです。
 *
 * @package net.agent.adapter
 */
class PHI_UserAgentAndroidTabletAdapter extends PHI_UserAgentAdapter
{
  /**
   * アダプタ名。
   */
  const ADAPTER_NAME = 'AndroidTablet';

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
  public function isAndroidTablet()
  {
    return TRUE;
  }

  /**
   * @see PHI_UserAgentAdapter::isTablet()
   */
  public function isTablet()
  {
    return TRUE;
  }

  /**
   * @see PHI_UserAgent::isValid()
   */
  public static function isValid($userAgent)
  {
    if (preg_match('/Android/', $userAgent) && strpos($userAgent, 'Mobile') === FALSE) {
      return TRUE;
    }

    return FALSE;
  }
}
