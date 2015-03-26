<!DOCTYPE html>
<html>
<head><?php $html->includeTemplate('/includes/head'); ?></head>
<body class="body-class-home body-class-blog">
  <div id="wrapper">
    <?php $html->includeTemplate('/includes/header'); ?>
    <div id="login" class="container">
      <div class="row">
        <div class="hidden-xs hidden-sm col-md-4"><img class="featurette-image img-responsive" alt="" src="/assets/base/phi/images/logo2.png"></div>
        <div class="col-xs-12 col-sm-8">
        <?php echo $html->errors(FALSE); ?>
          <h2 class="featurette-heading">ログ<span class="text-muted">イン</span></h2>
          <?php echo $form->start(array('action' => 'Login', 'route' => 'moduleRoute')); ?>
            <input id="loginPassword" name="loginPassword" class="form-control" type="password" placeholder="Enter your password">
            <p class="lead"><?php echo $form->inputSubmit('ログイン', array('class' => 'btn')); ?></p>
          <?php echo $form->close(); ?>
        </div>
      </div>
    </div>
    <?php $html->includeTemplate('includes/footer'); ?>
  </div>
</body>
</html>
