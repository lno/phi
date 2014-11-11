<?php
/**
 * DoCoMo 端末のためのユーザエージェントアダプタです。
 *
 * @package net.agent.adapter
 */
class PHI_UserAgentDoCoMoAdapter extends PHI_UserAgentAdapter
{
  /**
   * アダプタ名。
   */
  const ADAPTER_NAME = 'DoCoMo';

  /**
   * @return string
   */
  public function getAdapterName()
  {
    return self::ADAPTER_NAME;
  }

  /**
   * @see PHI_UserAgentAdapter::isDoCoMo()
   */
  public function isDoCoMo()
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
    if (preg_match('/^DoCoMo/', $userAgent)) {
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
   * @see PHI_UserAgentAdapter::getContentType()
   */
  public function getContentType()
  {
    return 'application/xhtml+xml; charset=' . $this->getEncoding();
  }

  /**
   * ユーザ ID (契約者 ID、別名 DoCoMo ID) を取得します。
   *   - ユーザ ID は大文字小文字を区別します。
   *   - SSL 通信時 (https) はユーザ ID を取得することができません。
   *   - ユーザ ID を識別する GUID は、ユーザエージェントが DoCoMo の場合自動的に URL に追加されます。
   *
   * @return string ユーザ ID を返します。
   *   ユーザ ID を取得できない (またはユーザが ID の通知を無効に設定している) 場合は NULL を返します。
   */
  public function getUserId()
  {
    $request = PHI_FrontController::getInstance()->getRequest();

    return $request->getEnvironment('HTTP_X_DCMGUID');
  }

  /**
   * 個体識別番号 (move は 11 桁、FOMA は 15 桁の英数字) を取得します。
   * 個体識別番号を取得する際はタグの属性に 'utn' を追加する必要があります。
   * <code>
   * <a href="..." utn>GetUTN</a>
   * <form action="..." utn>...</form>
   * </code>
   *
   * @return string 個体識別番号を返します。
   */
  public function getSerialNumber()
  {
    if (preg_match('/ser([0-9a-zA-Z]+)/', $this->_userAgent, $matches)) {
      return $matches[1];
    }

    return NULL;
  }
}
