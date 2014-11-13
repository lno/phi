<?php foreach ($menus as $package => $names): ?>
  <h2><?php echo $package ?></h2>
  <ul>
    <?php foreach ($names as $name => $attributes): ?>
      <li><?php echo $html->link($name, $relativeAPIPath . $attributes['anchor'], array('title' => $name)) ?></li>
    <?php endforeach ?>
  </ul>
<?php endforeach ?>