<?php
/**
 * AU 端末のためのユーザエージェントアダプタです。
 *
 * @package net.agent.adapter
 */
class PHI_UserAgentAUAdapter extends PHI_UserAgentAdapter
{
  /**
   * アダプタ名。
   */
  const ADAPTER_NAME = 'AU';

  /**
   * @return string
   */
  public function getAdapterName()
  {
    return self::ADAPTER_NAME;
  }

  /**
   * @see PHI_UserAgentAdapter::isAU()
   */
  public function isAU()
  {
    return TRUE;
  }

  /**
   * @see PHI_UserAgentAdapter::isMobile()
   */
  public function isMobile()
  {
    return TRUE;
  }

  /**
   * @see PHI_UserAgent::isValid()
   */
  public static function isValid($userAgent)
  {
    if (preg_match('/^UP\.Browser|^KDDI/', $userAgent)) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * @see PHI_UserAgentAdapter::getEncoding()
   */
  public function getEncoding()
  {
    return 'SJIS-win';
  }

  /**
   * EZ 番号 (29 桁の英数字) を取得します。
   *
   * @return string EZ 番号を返します。
   *   EZ 番号が取得できない (またはユーザが EZ 番号の通知を無効に設定している) 場合は NULL を返します。
   */
  public function getUserId()
  {
    $request = PHI_FrontController::getInstance()->getRequest();

    return $request->getEnvironment('HTTP_X_UP_SUBNO');
  }

  /**
   * {@link getUserId()} メソッドのエイリアスです。
   *
   * @deprecated このメソッドは将来的に破棄されます。
   */
  public function getSerialNumber()
  {
    return $this->getUserId();
  }
}
