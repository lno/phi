<!DOCTYPE html>
<html>
<head><?php $html->includeTemplate('/includes/head'); ?></head>
<body class="body-class-home body-class-blog">
  <div id="wrapper">
    <?php $html->includeTemplate('/includes/header'); ?>
    <div id="performance-analyzer-install" class="container">
      <div class="row">
        <div class="hidden-xs hidden-sm col-md-4"><img class="featurette-image img-responsive" alt="" src="/assets/base/phi/images/logo2.png"></div>
        <div class="col-xs-12 col-sm-8">
          <h2 class="featurette-heading" <?php if (!$html->hasError()) echo 'style="margin-top: 54px;"'?>>インストールの<span class="text-muted">結果</span></h2>
          <?php echo $html->messages(); ?>
          <?php echo $html->errors(); ?>
          <?php if (!$html->hasError()): ?>
            <p class="lead">以上で設定は完了です。今後はデータベースに送信されたクエリをパフォーマンスアナライザ画面から確認することができるようになります。</p>
            <?php echo $form->start('PerformanceAnalyzer') ?>
              <?php echo $form->inputSubmit('利用を開始する', array('class' => 'btn')); ?>
            <?php echo $form->close(); ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php $html->includeTemplate('includes/footer'); ?>
  </div>
</body>
</html>