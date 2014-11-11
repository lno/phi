<?php
/**
 * iPhone 端末のためのユーザエージェントアダプタです。
 *
 * @package net.agent.adapter
 */
class PHI_UserAgentIPhoneAdapter extends PHI_UserAgentAdapter
{
  /**
   * アダプタ名。
   */
  const ADAPTER_NAME = 'iPhone';

  /**
   * @return string
   */
  public function getAdapterName()
  {
    return self::ADAPTER_NAME;
  }

  /**
   * @see PHI_UserAgentAdapter::isIPhone()
   */
  public function isIPhone()
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
    if (preg_match('/iPhone/', $userAgent)) {
      return TRUE;
    }

    return FALSE;
  }
}
