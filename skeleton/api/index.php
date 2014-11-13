<!DOCTYPE html>
<html lang="ja">
<head>
  <?php echo $html->includeTemplate('includes/head') ?>
</head>
<body class="body-class-home body-class-blog">
  <div id="wrapper">
    <?php echo $html->includeTemplate('includes/header') ?>
    <div class="container" style="padding-top: 60px; margin-bottom: 100px;">
      <div class="row">
        <div class="col-md-3"
             style="font-size: 16px; padding: 0;" id="side-nav">
          <?php echo $menuTag ?>
        </div>
        <div class="col-md-9"
             style="font-size: 16px; background: white; padding: 50px; margin-top: -60px; border-radius: 0 0 50px 50px;"
             id="article">
          <h2>All packages</h2>
          <p>
            <?php foreach ($menus as $package => $names): ?>
              <?php echo $html->link($package, '#package_' . $package) ?>
            <?php endforeach ?>
          </p>
          <br>
          <br>
          <table class="table">
            <colgroup>
              <col class="col-package" />
              <col class="col-package-name" />
              <col class="col-package-summary" />
            </colgroup>
            <tr>
              <th>Package</th>
              <th>Name</th>
              <th>Summary</th>
            </tr>
            <?php foreach ($menus as $package => $names): ?>
              <?php $name = key($names) ?>
              <tr>
                <td rowspan="<?php echo sizeof($names) ?>" id="package_<?php echo $package ?>"><?php echo $package ?></td>
                <td><?php echo $html->link($name, $relativeAPIPath . $names[$name]['anchor']) ?></td>
                <td>
                  <?php if (isset($names[$name]['summary'])): ?>
                    <?php echo $document->decorateText($names[$name]['summary']) ?>
                  <?php endif ?>
                </td>
              </tr>
              <?php next($names) ?>
              <?php while (list($name, $attributes) = each($names)): ?>
                <tr>
                  <td><?php echo $html->link($name, $relativeAPIPath . $attributes['anchor']) ?></td>
                  <td>
                    <?php if (isset($attributes['summary'])): ?>
                      <?php echo $document->decorateText($attributes['summary']) ?>
                    <?php endif ?>
                  </td>
                </tr>
              <?php endwhile ?>
            <?php endforeach ?>
          </table>
<!--          <p class="right"><a href="#top">Top</a></p>-->
        </div>
      </div>
    </div>
    <?php echo $html->includeTemplate('includes/footer') ?>
  </div>
</body>
</html>
