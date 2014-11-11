<?php
/**
 * @package modules.manager.actions
 */
class MemberProfileAction extends PHI_Action
{
  public function execute()
  {
    $memberId = $this->getRequest()->getParameter('memberId');
    $member = PHI_DAOFactory::create('Members')->findByMemberId($memberId);

    // デモアプリケーション上、会員が存在しない場合の処理は行っていない
    // 通常は例外を発生させるか、テンプレート側でエラーを表示する
    if ($member) {
      $view = $this->getView();
      $view->setAttribute('mailAddress', $member->mailAddress);
      $view->setAttribute('nickname', $member->nickname);
      $view->setAttribute('birthDate', $member->birthDate);
      $view->setAttribute('blood', $member->blood);
      $view->setAttribute('hobbies', $member->hobbies);
      $view->setAttribute('message', $member->message);

    } else {
      throw new PHI_DataNotFoundException('データがありません。');
    }

    return PHI_View::SUCCESS;
  }
}
