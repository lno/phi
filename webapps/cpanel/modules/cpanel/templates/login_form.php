<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta http-equiv="Content-Language" content="ja" />
    <meta charset="UTF-8" />
    <title>cPanel - ログイン</title>
    <?php echo $html->includeCSS('/assets/base/phi/css/base.css') ?>
    <!--[if lt IE 9]>
    <script src="/assets/js/html5shiv.js"></script>
    <![endif]-->
  </head>
  <body>
    <header>
      <?php $html->includeTemplate('includes/header'); ?>
      <h1>ログイン</h1>
    </header>
    <div id="contents">
      <?php echo $form->start('Login') ?>
        <?php echo $html->errors(FALSE) ?>
        <?php echo $form->inputPassword('loginPassword', array('size' => 20), array('label' => 'パスワード')) ?>
        <p><?php echo $form->inputSubmit('ログイン', array('class' => 'btn')) ?></p>
      <?php echo $form->close() ?>
    </div>
    <footer>
      <?php $html->includeTemplate('includes/footer') ?>
    </footer>
  </body>
</html>
