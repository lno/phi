<!DOCTYPE html>
<html lang="ja">
<head>
  <?php echo $html->includeTemplate('includes/head') ?>
</head>
<body class="body-class-home body-class-blog">
  <div id="wrapper">
    <?php echo $html->includeTemplate('includes/header') ?>
    <div class="container docs">
      <?php echo $html->includeTemplate('includes/'.$contentTemplateName) ?>
    </div>
    <?php echo $html->includeTemplate('includes/footer') ?>
  </div>
</body>
</html>