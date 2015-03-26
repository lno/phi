<!DOCTYPE html>
<html>
<head><?php $html->includeTemplate('/includes/head'); ?></head>
<body class="body-class-home body-class-blog">
  <div id="wrapper">
    <?php $html->includeTemplate('/includes/header'); ?>
    <div id="cache-manager" class="container">
      <div class="row">
        <div class="hidden-xs hidden-sm col-md-4"><img class="featurette-image img-responsive" alt="" src="/assets/base/phi/images/logo2.png"></div>
        <div class="col-xs-12 col-sm-8">
          <h2 class="featurette-heading">キャッシュ<span class="text-muted">管理</span></h2>
          <p class="lead">クラスロードキャッシュ、テンプレートキャッシュ、コンフィグレーションキャッシュのうちいずれか、もしくは全てを削除することができます。</p>
        </div>
      </div>
      <div class="base">
        <div class="row">
          <?php echo $form->start('CacheClearDispatcher') ?>
            <?php echo $html->messages() ?>
            <table class="table table-striped">
              <colgroup>
                <col width="50%">
                <col width="20%">
                <col width="30%">
              </colgroup>
              <tr>
                <th class="left">クラスロードキャッシュ</th>
                <td class="right"><?php echo $fileCacheSize ?> KB</td>
                <td><?php echo $form->inputSubmit('削除', array('name' => 'dispatchClearFileCache', 'class' => 'btn')) ?></td>
              </tr>
              <tr>
                <th class="left">テンプレートキャッシュ</th>
                <td class="right"><?php echo $templatesCacheSize ?> KB</td>
                <td><?php echo $form->inputSubmit('削除', array('name' => 'dispatchClearTemplatesCache', 'class' => 'btn')) ?></td>
              </tr>
              <tr>
                <th class="left">コンフィグレーションキャッシュ</th>
                <td class="right"><?php echo $yamlCacheSize ?> KB</td>
                <td><?php echo $form->inputSubmit('削除', array('name' => 'dispatchClearYamlTemplatesCache', 'class' => 'btn')) ?></td>
              </tr>
              <tr>
                <th class="left">合計</th>
                <td class="right"><?php echo $fileCacheSize + $templatesCacheSize + $yamlCacheSize ?> KB</td>
                <td><?php echo $form->inputSubmit('全て削除', array('name' => 'dispatchClearAllCache', 'class' => 'btn')) ?></td>
              </tr>
            </table>
          <?php echo $form->close() ?>
        </div>
      </div>
    </div>
    <?php $html->includeTemplate('includes/footer'); ?>
  </div>
</body>
</html>
