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
              <select class="form-control" name="namespace" id="namespace">
                <?php foreach ($namespaceList as $namespace): ?>
                  <option value="<?php echo $namespace; ?>"><?php echo $namespace; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <hr class="divider1">
          <h2>対象テーブル</h2>
          <div class="row">
            <div class="col-md-6">
              <select multiple class="form-control" name="tables[]"  id="tables">
                <?php foreach ($tables as $table): ?>
                <option value="<?php echo $table; ?>"><?php echo $table; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <hr class="divider1">
          <h2>生成クラス</h2>
          <?php echo $form->inputCheckboxes('createType', $createType, NULL, array('error' => FALSE)); ?>
          <hr class="divider1">
          <h2>DAO 基底クラス</h2>
          <div class="row">
            <div class="col-md-3">
              <input type="text" class="form-control" name="baseDAOClassName" value="PHI_DAO" id="baseDAOClassName">
            </div>
          </div>
          <hr class="divider1">
          <h2>エンティティ基底クラス</h2>
          <div class="row">
            <div class="col-md-3">
              <input type="text" class="form-control" name="baseEntityClassName" value="PHI_DatabaseEntity" id="baseEntityClassName">
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
        $("#row_base_dao_class_name").hide();
      }

      if (!$("#createType_entity").attr("checked")) {
        $("#row_base_entity_class_name").hide();
      }

      $("#namespace").change(function() {
        this.form.action = '/cpanel/generateDAOForm.do';
        this.form.submit();
      });

      $("#createType_dao").click(function() {
        $("#row_base_dao_class_name").fadeToggle();
      });

      $("#createType_entity").click(function() {
        $("#row_base_entity_class_name").fadeToggle();
      });
    });
  </script>
</body>
</html>