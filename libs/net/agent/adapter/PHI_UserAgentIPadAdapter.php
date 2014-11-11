<?php
/**
 * iPad 端末のためのユーザエージェントアダプタです。
 *
 * @package net.agent.adapter
 */
class PHI_UserAgentIPadAdapter extends PHI_UserAgentAdapter
{
  /**
   * アダプタ名。
   */
  const ADAPTER_NAME = 'iPad';

  /**
   * @return string
   */
  public function getAdapterName()
  {
    return self::ADAPTER_NAME;
  }

  /**
   * @see PHI_UserAgentAdapter::isIPad()
   */
  public function isIPad()
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
    if (preg_match('/iPad/', $userAgent)) {
      return TRUE;
    }

    return FALSE;
  }
}
