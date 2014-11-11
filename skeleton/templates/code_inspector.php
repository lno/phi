<dl class="phi-stack-traces">
  <?php foreach ($traces as $trace): ?>
    <?php if ($trace['isOutput']): ?>
    <dt id="trace_point_<?php echo $trace['traceId'] ?>">
      <?php echo $trace['title'] ?><br />
      <span class="phi-file-info"><?php echo $trace['file'] ?></span>
    </dt>
    <?php if ($trace['isExpand']): ?>
    <dd id="trace_point_detail_<?php echo $trace['traceId'] ?>" class="phi-code-inspector" style="display: block">
    <?php else: ?>
    <dd id="trace_point_detail_<?php echo $trace['traceId'] ?>" class="phi-code-inspector" style="display: none">
    <?php endif ?>
      <h2>Inspector code</h2>
      <p class="phi-stack-trace lang-php"><?php echo $trace['code'] ?></p>
    </dd>
    <?php endif ?>
  <?php endforeach ?>
</dl>
