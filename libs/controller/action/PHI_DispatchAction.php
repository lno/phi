<?php
/**
 * 1 つのフォームに複数の submit が存在する場合、実行するアクションへのリレーを行うディスパッチャです。
 *
 * @package controller.action
 */
abstract class PHI_DispatchAction extends PHI_Action
{
  /**
   * デフォルトのフォワード先を取得します。
   *
   * @return string デフォルトのフォワード先を返します。
   * @throws PHI_ForwardException フォワード先が不明な場合に発生。
   */
  abstract public function defaultForward();

  /**
   * ディスパッチ先のアクションを取得、フォワード処理を行います。
   */
  public function execute()
  {
    $parameters = $this->getRequest()->getParameters();
    $executeMethod = NULL;

    foreach ($parameters as $name => $value) {
      $imagePos = strrpos($name, '_x');

      if ($imagePos && strlen($name) - 2) {
        $executeMethod = substr($name, 0, -2);
        break;
      }

      if (strpos($name, 'dispatch') === 0) {
        $executeMethod = $name;
        break;
      }
    }

    if ($executeMethod !== NULL && method_exists($this, $executeMethod)) {
      $forward = $this->$executeMethod();

      if (is_array($forward)) {
        $this->getController()->forward($forward[0], $forward[1]);
      } else {
        $this->getController()->forward($forward);
      }

    } else {
      $forward = $this->defaultForward();

      if ($forward) {
        $this->getController()->forward($forward);
      }
    }

    return PHI_View::NONE;
  }
}
