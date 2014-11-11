<?php
/**
 * @package modules.entry.actions
 */
class MemberRegisterAction extends PHI_Action
{
  public function validate()
  {
    // 正当なトークンでリクエストされているかチェック
    $tokenState = $this->getUser()->getTokenState(TRUE);
    $result = FALSE;

    if ($tokenState == PHI_AuthorityUser::TOKEN_VALID) {
      $result = TRUE;

    } else if ($tokenState  == PHI_AuthorityUser::TOKEN_INVALID) {
      $this->getMessages()->addError('登録は完了済みです。');
      $this->getForm()->clear();

    } else {
      $this->getMessages()->addError('不正な画面遷移です。');
      $this->getForm()->clear();
    }

    return $result;
  }

  public function execute()
  {
    // フィールドデータをエンティティに変換
    $membersDAO = PHI_DAOFactory::create('Members');
    $member = $membersDAO->formToEntity();

    $form = $this->getForm();

    $birthDate = implode('/', $form->get('birth'));
    $passwordHash = PHI_StringUtils::buildHash($form->get('loginPassword'));
    $hobbies = array_sum($form->get('hobbies'));

    $member->loginPassword = $passwordHash;
    $member->birthDate = $birthDate;
    $member->hobbies = $hobbies;
    $member->registerDate = new PHI_DatabaseExpression('NOW()');

    $membersDAO->insert($member);
    $entity = $membersDAO->findByMailAddress($member->mailAddress);

    $tokenId = $form->get('tokenId');

    $service = $this->getService('Member');
    $writePath = $service->getIconPath($entity->memberId);
    $previewPath = $service->getIconPreviewPath($tokenId);

    if (is_file($previewPath)) {
      PHI_FileUtils::move($previewPath, $writePath);
    }

    $this->getMessages()->add('会員登録が完了しました。');

    return PHI_View::SUCCESS;
  }
}
