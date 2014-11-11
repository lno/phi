<?php if ($functionCallCount == 1): ?>
  <script type="text/javascript" src="/assets/base/require.js"></script>
  <script type="text/javascript" src="/assets/base/phi/js/code_inspector.js"></script>
<?php endif ?>
<div class="phi-context">
  <div class="phi-debug-output">
    <p class="phi-debug-output-header">dprint() #<?php echo $functionCallCount ?></p>
    <div class="phi-debug-output-container">
      <?php echo $code ?>
      <dl class="phi-debug-output-message">
        <dt>Type:</dt>
        <dd><?php echo $type ?></dd>
        <?php if (isset($length)): ?>
          <dt>Length:</dt>
          <dd><?php echo number_format($length) ?></dd>
        <?php endif ?>
        <dt>Result:</dt>
        <dd><pre><code><?php echo $message ?></code></pre></dd>
      </dl>
    </div>
  </div>
</div>
