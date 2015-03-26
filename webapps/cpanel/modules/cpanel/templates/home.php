<!DOCTYPE html>
<html>
<head><?php $html->includeTemplate('/includes/head'); ?></head>
<body class="body-class-home body-class-blog">
  <div id="wrapper">
    <?php $html->includeTemplate('/includes/header'); ?>
    <div class="container">
      <div class="row">
        <div class="hidden-xs hidden-sm col-md-4"><img class="featurette-image img-responsive" alt="" src="/assets/base/phi/images/logo2.png"></div>
        <div class="col-xs-12 col-sm-8">
          <h2 class="featurette-heading">コントロール<span class="text-muted">パネル</span></h2>
          <p class="lead">開発を支援する機能を利用することができます。<br /></p>
        </div>
      </div>
      <div class="base">
        <div class="row">
          <div class="col-xs-12 col-sm-6 col-md-4">
            <a href="/cpanel/cacheManager.do">
              <img class="img-circle img-responsive" src="/assets/base/phi/images/screenshot/start.png" alt="">
              <h2>キャッシュ管理</h2>
            </a>
            <p>クラスロードキャッシュ、テンプレートキャッシュ、コンフィグレーションキャッシュのうちいずれか、もしくは全てを削除することができます。</p>
          </div>
          <div class="col-xs-12 col-sm-6 col-md-4">
            <a href="/cpanel/performanceAnalyzer.do">
              <img class="img-circle img-responsive" src="/assets/base/phi/images/screenshot/dao.png" alt="">
              <h2>DAOジェネレータ</h2>
            </a>
            <p>アプリケーションに設定されたデータベースを参照し、DAO・エンティティクラスを自動生成することができます。</p>
          </div>
          <div class="col-xs-12 col-sm-6 col-md-4">
            <a href="/cpanel/generateDAOForm.do">
              <img class="img-circle img-responsive" src="/assets/base/phi/images/screenshot/performance_analyzer1.png" alt="">
              <h2>パフォーマンスアナライザ</h2>
            </a>
            <p>パフォーマンスアナライザはアプリケーション開発者のためのパフォーマンス計測ツールです。遅いアクションやクエリを特定し、チューニングを行うことが可能です。</p>
          </div>
        </div>
      </div>
    </div>
    <?php $html->includeTemplate('includes/footer'); ?>
  </div>
</body>
</html>
