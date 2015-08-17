<!DOCTYPE html>
<html>
<head><?php $html->includeTemplate('/includes/head'); ?></head>
<body class="body-class-home body-class-blog">
  <div id="wrapper">
    <?php $html->includeTemplate('/includes/header'); ?>
    <div id="dao-deploy" class="container">
      <div class="row">
        <div class="hidden-xs hidden-sm col-md-4"><img class="featurette-image img-responsive" alt="" src="/assets/base/phi/images/logo2.png"></div>
        <div class="col-xs-12 col-sm-8">
          <h2 class="featurette-heading">スケルトンファイルの<span class="text-muted">デプロイ完了</span></h2>
          <p class="lead">デプロイが正常に完了しました。</p>
        </div>
      </div>
      <div class="base">
        <table class="table table-striped">
          <colgroup>
            <col width="15%">
            <col width="85%">
          </colgroup>
          <tr>
            <th class="left">エンティティ</th>
            <td class="right">
              <?php if ($entities->count()): ?>
                <?php echo $html->ul($entities) ?>
              <?php else: ?>
                更新対象ファイルがありません。
              <?php endif; ?>
            </td>
          </tr>
          <tr>
            <th class="left">DAO</th>
            <td class="right">
              <?php if ($dataAccessObjects->count()): ?>
                <?php echo $html->ul($dataAccessObjects) ?>
              <?php else: ?>
                更新対象ファイルがありません。
              <?php endif; ?>
            </td>
          </tr>
        </table>
        <button class="btn" onclick="location.href='/cpanel/generateDAOForm.do';">戻る</button>
      </div>
    </div>
    <?php $html->includeTemplate('includes/footer'); ?>
  </div>
</body>
</html>