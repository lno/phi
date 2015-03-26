<!DOCTYPE html>
<html>
<head><?php $html->includeTemplate('/includes/head'); ?></head>
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
        <h2>インストール方法</h2>
        <p>パフォーマンスアナライザを利用するにはデータベース (MySQL) の設定が必要となります。以下のコードを参考にパフォーマンスアナライザに必要な属性を config/application.yml に追記して下さい。</p>
        <br />
        <pre class="language-php line-numbers">
          <code># データベース接続情報<br />
          database:<br />
          &nbsp;&nbsp;default:<br />
          &nbsp;&nbsp;&nbsp;&nbsp;dsn: "mysql:host=localhost; dbname={DB_NAME}; port={PORT}"<br />
          &nbsp;&nbsp;&nbsp;&nbsp;user: "{DB_USER}"<br />
          &nbsp;&nbsp;&nbsp;&nbsp;password: "{DB_PASSWORD}"<br /><br />
          # パフォーマンスアナライザの設定<br />
          observer:<br />
          &nbsp;&nbsp;listeners:<br />
          &nbsp;&nbsp;&nbsp;&nbsp;# リスナー ID (固定)<br />
          &nbsp;&nbsp;&nbsp;&nbsp;performanceListener:<br />
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;class: PHI_PerformanceListener<br />
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;dataSource: default</code>
        </pre>
        <br />
        <p>ファイル更新後、「パフォーマンスアナライザをインストールする」ボタンを押してインストールを完了させましょう。</p>
        <br />
        <?php echo $form->start('PerformanceAnalyzerInstall') ?>
          <p class="center"><?php echo $form->inputSubmit('パフォーマンスアナライザをインストールする', array('class' => 'btn')) ?></p>
        <?php echo $form->close() ?>
      </div>
    </div>
    <?php $html->includeTemplate('includes/footer'); ?>
  </div>
</body>
</html>
