<!DOCTYPE html>
<html>
<head>
<?php $html->includeTemplate('/includes/head'); ?>
<?php echo $html->includeCSS('/assets/base/jquery-ui-1.8.16.custom/css/smoothness/jquery-ui-1.8.16.custom.css') ?>
<?php echo $html->includeCSS('/assets/base/tablesorter/style.css') ?>
<?php echo $html->includeCSS('/assets/base/phi/css/custom.css') ?>
<?php echo $html->includeCSS('/assets/base/phi/css/jquery_setup.css') ?>
<?php echo $html->includeJS('/assets/base/jquery-ui-1.8.16.custom/js/jquery-1.6.2.min.js') ?>
<?php echo $html->includeJS('/assets/base/jquery-ui-1.8.16.custom/js/jquery-ui-1.8.16.custom.min.js') ?>
<?php echo $html->includeJS('/assets/base/jquery-ui-1.8.16.custom/development-bundle/external/jquery.cookie.js') ?>
<?php echo $html->includeJS('/assets/base/tablesorter/jquery.tablesorter.min.js') ?>
<?php echo $html->includeJS('/assets/base/phi/js/analyze.js') ?>
</head>
<body class="body-class-home body-class-blog">
  <div id="wrapper">
    <?php $html->includeTemplate('/includes/header'); ?>
    <div id="performance-analyzer" class="container">
      <div class="row">
        <div class="hidden-xs hidden-sm col-md-4"><img class="featurette-image img-responsive" alt="" src="/assets/base/phi/images/logo2.png"></div>
        <div class="col-xs-12 col-sm-8">
          <h2 class="featurette-heading">パフォーマンス<span class="text-muted">アナライザ</span></h2>
          <p class="lead">パフォーマンスアナライザはアプリケーション開発者のためのパフォーマンス計測ツールです。<br />ビジネスロジックで必要とされる実行コストをグラフィカルに確認することができるため、アプリケーションを開発する上でボトルネックとなるロジックを見つけ出す際に非常に役立つでしょう。</p>
        </div>
      </div>
      <div class="base">
        <?php echo $form->start() ?>
          <div class="search">
            <?php echo $form->select('module', $modules, array('class' => 'form-control'), array('fieldTag' => '\1')) ?>
            <?php echo $form->inputText('from', array('class' => 'form-control'), array('fieldTag' => '\1')) ?>
            ～
            <?php echo $form->inputText('to', array('class' => 'form-control'), array('fieldTag' => '\1')) ?>
            <?php echo $form->inputSubmit('検索', array('name' => 'search', 'class' => 'btn')) ?>
          </div>
        <?php echo $form->close() ?>
        <div id="tabs">
          <ul>
            <li><?php echo $html->link('アクションの解析', array('action' => 'AnalyzeAction'), NULL, array('query' => array('target' => $form->get('module'), 'from' => $form->get('from'), 'to' => $form->get('to')))) ?></li>
            <li><?php echo $html->link('SQL の解析', array('action' => 'AnalyzeSQL'), NULL, array('query' => array('target' => $form->get('module'), 'type' => 'default', 'from' => $form->get('from'), 'to' => $form->get('to')))) ?></li>
            <li><?php echo $html->link('SQL の解析 (プリペアードステートメント)', array('action' => 'AnalyzeSQL'), NULL, array('query' => array('target' => $form->get('module'), 'type' => 'prepared', 'from' => $form->get('from'), 'to' => $form->get('to')))) ?></li>
            <li><?php echo $html->link('SQL レポート', array('action' => 'AnalyzeSQLReport'), NULL, array('query' => array('target' => $form->get('module'), 'from' => $form->get('from'), 'to' => $form->get('to')))) ?></li>
            <li><?php echo $html->link('設定', 'AnalyzeSettingForm') ?></li>
          </ul>
        </div>
      </div>
    </div>
    <?php $html->includeTemplate('includes/footer'); ?>
  </div>
</body>
</html>