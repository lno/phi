<?php
/**
 * Web アプリケーションの基底クラスです。
 *
 * @package controller
 */
abstract class PHI_WebApplication extends PHI_Object
{
  /**
   * @var PHI_FrontController
   */
  private $_controller;

  /**
   * コンストラクタ。
   */
  public function __construct()
  {
    $this->_controller = PHI_FrontController::getInstance();
  }

  /**
   * リクエスト (request) コンポーネントを取得します。
   *
   * @return PHI_HttpRequest PHI_HttpRequest を実装したオブジェクトのインスタンスを返します。
   * @see PHI_DIContainer::getComponent()
   */
  public function getRequest()
  {
    return $this->_controller->getRequest();
  }

  /**
   * セッション (session) コンポーネントを取得します。
   *
   * @return PHI_HttpSession PHI_HttpSession を実装したオブジェクトのインスタンスを返します。
   * @see PHI_DIContainer::getComponent()
   */
  public function getSession()
  {
    return $this->_controller->getRequest()->getSession();
  }

  /**
   * ユーザ (user) コンポーネントを取得します。
   *
   * @return PHI_AuthorityUser PHI_AuthorityUser を実装したオブジェクトのインスタンスを返します。
   * @see PHI_DIContainer::getComponent()
   */
  public function getUser()
  {
    return $this->_controller->getRequest()->getSession()->getUser();
  }

  /**
   * レスポンス (response) コンポーネントを取得します。
   *
   * @return PHI_HttpRequest PHI_HttpResponse を実装したオブジェクトのインスタンスを返します。
   * @see PHI_DIContainer::getComponent()
   */
  public function getResponse()
  {
    return $this->_controller->getResponse();
  }

  /**
   * ビュー (view) コンポーネントを取得します。
   *
   * @return PHI_View PHI_View を実装したオブジェクトのインスタンスを返します。
   * @see PHI_DIContainer::getComponent()
   */
  public function getView()
  {
    return $this->_controller->getResponse()->getView();
  }

  /**
   * メッセージオブジェクトを取得します。
   *
   * @return PHI_ActionMessages メッセージオブジェクトを返します。
   * @see PHI_DIContainer::getComponent()
   */
  public function getMessages()
  {
    return $this->_controller->getResponse()->getMessages();
  }

  /**
   * フォームオブジェクトを取得します。
   *
   * @return PHI_ActionForm フォームオブジェクトを返します。
   */
  public function getForm()
  {
    return PHI_ActionForm::getInstance();
  }

  /**
   * データベースマネージャを取得します。
   *
   * @return PHI_DatabaseManager データベースマネージャを返します。
   */
  public function getDatabase()
  {
    return PHI_DatabaseManager::getInstance();
  }
}
