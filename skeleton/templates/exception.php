<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta http-equiv="Content-Language" content="ja" />
    <meta charset="UTF-8" />
    <title><?php echo get_class($exception) ?></title>
    <link rel="stylesheet" type="text/css" href="/assets/base/phi/css/base.css" />
    <script type="text/javascript" src="/assets/base/require.js"></script>
    <script type="text/javascript" src="/assets/base/phi/js/code_inspector.js"></script>
    <!--[if lt IE 9]>
    <script src="/assets/js/html5shiv.js"></script>
    <![endif]-->
  </head>
  <body>
    <header>
      <h1><?php printf('%s: %s', get_class($exception), $message) ?></h1>
    </header>
    <div id="contents" class="phi-context">
      <?php if ($exception instanceof PHI_Exception && $exception->hasTrigger()): ?>
        <dl class="phi-exception-trigger">
          <dt>Trigger code:</dt>
          <dd>
            <?php if ($exception->getTriggerCodeType() === 'php'): ?>
              <?php echo PHI_DebugUtils::syntaxHighlight($exception->getTriggerCode(), array('format' => array('target' => $exception->getTriggerLine()))) ?>
            <?php else: ?>
              <?php echo $exception->getTriggerCode() ?>
            <?php endif ?>
          </dd>
        </dl>
      <?php endif ?>

      <?php echo $trace ?>
    </div>
  </body>
</html>
