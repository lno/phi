<?php
/**
 * PC (携帯、スマートフォンを除く) 端末のためのユーザエージェントアダプタです。
 *
 * @package net.agent.adapter
 */
class PHI_UserAgentDefaultAdapter extends PHI_UserAgentAdapter
{
  /**
   * アダプタ名。
   */
  const ADAPTER_NAME = 'Default';

  /**
   * @return string
   */
  public function getAdapterName()
  {
    return self::ADAPTER_NAME;
  }

  /**
   * @see PHI_UserAgent::isValid()
   */
  public static function isValid($userAgent)
  {
    $adapters = PHI_UserAgent::getAdapters();

    foreach ($adapters as $className) {
      if (call_user_func(array($className, 'isValid'), $userAgent)) {
        return FALSE;
      }
    }

    return TRUE;
  }

  /**
   * @see PHI_UserAgentAdapter::isDefault()
   */
  public function isDefault()
  {
    return TRUE;
  }
}
