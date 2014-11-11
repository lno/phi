<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta http-equiv="Content-Language" content="ja" />
    <meta charset="UTF-8" />
    <title>cPanel - DAO ジェネレータ</title>
    <?php echo $html->includeCSS('/assets/base/phi/css/base.css') ?>
    <!--[if lt IE 9]>
    <script src="/assets/js/html5shiv.js"></script>
    <![endif]-->
  </head>
  <body>
    <header>
      <?php $html->includeTemplate('includes/header'); ?>
      <h1>DAO ジェネレータ</h1>
    </header>
    <div id="contents">
      <p class="right"><?php echo $html->link('戻る', 'GenerateDAOForm') ?></p>
      <h2>スケルトンファイルのデプロイ完了</h2>
      <p>デプロイが正常に完了しました。</p>
      <dl>
        <dt>エンティティ</dt>
        <dd>
          <?php if ($entities->count()): ?>
            <?php echo $html->ul($entities) ?>
          <?php else: ?>
            更新対象ファイルがありません。
          <?php endif; ?>
        </dd>
        <dt>DAO</dt>
        <dd>
          <?php if ($dataAccessObjects->count()): ?>
            <?php echo $html->ul($dataAccessObjects) ?>
          <?php else: ?>
            更新対象ファイルがありません。
          <?php endif; ?>
        </dd>
      </dl>
    </div>
    <footer>
      <?php $html->includeTemplate('includes/footer') ?>
    </footer>
  </body>
</html>
