<?php
/**
 * @package modules.manager.actions
 */
class MemberListAction extends PHI_Action
{
  public function execute()
  {
    // 会員リストをデータベースから取得してページャに変換
    PHI_DAOFactory::create('Members')->findToPager()->assignView();

    return PHI_View::SUCCESS;
  }
}
