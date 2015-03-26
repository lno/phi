<!DOCTYPE html>
<html>
<head><?php $html->includeTemplate('/includes/head'); ?></head>
<body class="body-class-home body-class-blog">
  <div id="wrapper">
    <?php $html->includeTemplate('/includes/header'); ?>
    <div id="generate-dao-form" class="container">
      <div class="row">
        <div class="hidden-xs hidden-sm col-md-4"><img class="featurette-image img-responsive" alt="" src="/assets/base/phi/images/logo2.png"></div>
        <div class="col-xs-12 col-sm-8">
          <h2 class="featurette-heading">DAO<span class="text-muted">ジェネレータ</span></h2>
          <p class="lead">アプリケーションに設定されたデータベースを参照し、DAO・エンティティクラスを自動生成することができます。</p>
        </div>
      </div>
      <div class="base">
        <?php if (!$html->hasError()): ?>
          <?php echo $html->errors(); ?>
        <?php endif; ?>
        <?php echo $form->start('GenerateDAO') ?>
          <h2 class="top">参照データベース</h2>
          <div class="row">
            <div class="col-md-3">
              <?php echo $form->select('namespace', $namespaceList, array('class' => 'form-control')) ?>
            </div>
          </div>
          <hr class="divider1">
          <h2>対象テーブル</h2>
          <div class="row">
            <div class="col-md-6">
              <?php echo $form->select('tables', array('output' => $tables, 'values' => $tables), array('class' => 'form-control', 'multiple' => 'multiple', 'size' => 10), array('error' => FALSE)) ?>
            </div>
          </div>
          <hr class="divider1">
          <h2>生成クラス</h2>
          <?php echo $form->inputCheckboxes('createType', $createType, NULL, array('error' => FALSE)); ?>
        <div id="row-base-dao-class-name">
          <hr class="divider1">
          <h2>DAO 基底クラス</h2>
          <div class="row">
            <div class="col-md-3">
              <?php echo $form->inputText('baseDAOClassName', array('class' => 'form-control')) ?>
            </div>
          </div>
        </div>
        <div id="row-base-entity-class-name">
          <hr class="divider1">
          <h2>エンティティ基底クラス</h2>
          <div class="row">
            <div class="col-md-3">
              <?php echo $form->inputText('baseEntityClassName', array('class' => 'form-control')) ?>
            </div>
          </div>
        </div>
          <?php echo $form->inputSubmit('作成', array('class' => 'btn')); ?>
        <?php echo $form->close(); ?>
      </div>
    </div>
    <?php $html->includeTemplate('includes/footer'); ?>
  </div>
  <script type="text/javascript">
    $(document).ready(function(){
      if (!$("#createType_dao").attr("checked")) {
        $("#row-base-dao-class-name").hide();
      }

      if (!$("#createType_entity").attr("checked")) {
        $("#row-base-entity-class-name").hide();
      }

      $("#namespace").change(function() {
        this.form.action = '/cpanel/generateDAOForm.do';
        this.form.submit();
      });

      $("#createType_dao").click(function() {
        $("#row-base-dao-class-name").fadeToggle();
      });

      $("#createType_entity").click(function() {
        $("#row-base-entity-class-name").fadeToggle();
      });
    });
  </script>
</body>
</html>