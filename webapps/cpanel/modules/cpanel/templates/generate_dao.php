<!DOCTYPE html>
<html>
<head><?php $html->includeTemplate('/includes/head'); ?></head>
<body class="body-class-home body-class-blog">
  <div id="wrapper">
    <?php $html->includeTemplate('/includes/header'); ?>
    <div id="generate-dao" class="container">
      <div class="row">
        <div class="hidden-xs hidden-sm col-md-4"><img class="featurette-image img-responsive" alt="" src="/assets/base/phi/images/logo2.png"></div>
        <div class="col-xs-12 col-sm-8">
          <h2 class="featurette-heading">スケルトン<span class="text-muted">生成完了</span></h2>
          <p class="lead">
            スケルトンファイルを一時ディレクトリ下に生成しました。<br />
            生成されたファイルを libs 下に手動でコピーするか、もしくは画面下のデプロイボタンを押下して下さい。<br />
            デプロイボタンを押下した場合、エンティティに関しては全て上書きされますが、DAO に関しては既に存在するファイルを上書きしません。<br />
            またデプロイ完了後は一時ファイルを自動的に削除します。
          </p>
        </div>
      </div>
      <div class="base">
        <?php echo $form->start('DAODeploy') ?>
          <table class="table table-striped">
            <colgroup>
              <col width="15%">
              <col width="85%">
            </colgroup>
            <?php if ($entities->count()): ?>
              <tr>
                <th class="left">エンティティ</th>
                <td class="right">
                  <?php foreach ($entities as $current): ?>
                    <?php echo $current['relative'] ?> <?php echo $form->inputHidden('entities[]', array('value' => $current['file'])) ?>
                    <br />
                  <?php endforeach; ?>
                </td>
              </tr>
            <?php endif; ?>
            <?php if ($dataAccessObjects->count()): ?>
              <tr>
                <th class="left">DAO</th>
                <td class="right">
                  <?php foreach ($dataAccessObjects as $current): ?>
                    <?php echo $current['relative'] ?> <?php echo $form->inputHidden('dataAccessObjects[]', array('value' => $current['file'])) ?>
                    <br />
                  <?php endforeach; ?>
                </td>
              </tr>
            <?php endif; ?>
          </table>
          <p class="center">
            <?php echo $form->inputSubmit('ファイルをデプロイする', array('class' => 'btn')) ?>
          </p>
        <?php echo $form->close() ?>
      </div>
    </div>
    <?php $html->includeTemplate('includes/footer'); ?>
  </div>
</body>
</html>