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