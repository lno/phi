<!DOCTYPE html>
<html lang="ja">
<head>
  <?php echo $html->includeTemplate('includes/head') ?>
</head>
<body class="body-class-home body-class-blog">
  <div id="wrapper">
    <?php echo $html->includeTemplate('includes/header') ?>
    <div class="container docs">
      <div class="row">
        <div class="col-md-3" id="side-nav">
          <?php echo $menuTag ?>
        </div>
        <div class="col-md-9" id="article">
          <?php echo $contentTag ?>
        </div>
      </div>
    </div>
    <?php echo $html->includeTemplate('includes/footer') ?>
  </div>
</body>
</html>
