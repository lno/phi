<?php
/**
 * ロギングしたメッセージをファイルへ書き込みます。
 *
 * application.yml の設定例:
 * <code>
 * logger:
 *   # ログアペンダ ID。
 *   {appender_name}:
 *     # ロガークラス名。
 *     class: PHI_LoggerFileAppender
 *
 *     # ログの出力先。絶対パス、または ({APP_ROOT_DIR}/logs からの) 相対パスでの指定が可能。
 *     # 'error.log' を指定した場合の出力先は {APP_ROOT_DIR}/logs/error.log となる。
 *     file: logs/phi.log
 *
 *     # ログファイルをローテートする際に用いる属性。
 *     rotate:
 *       # {@link PHI_LogRotatePolicy::setGeneration()}
 *       generation: 4
 *
 *       # {@link PHI_LogWriter::__construct()}
 *       appendMode: TRUE
 *
 *       # {@link PHI_LogWriter::setLinefeed()}
 *       linefeed: <?php echo PHP_EOL ?>
 *
 *       # ファイル作成時の権限を 8 進数で指定。(例えば 0644)
 *       # 未指定時は OS (実行ユーザ) のファイル書き込み権限に依存する。
 *       mode:
 *
 *       # ログのローテートタイプ。
 *       #   - date: 日付によるローテート。
 *       #   - size: ファイルサイズによるローテート。
 *       type:
 *
 *       ############################################################
 *       # type が 'date' の場合に指定可能なオプション
 *       ############################################################
 *       # {@link PHI_LogRotateDateBasedPolicy::setDatePattern()}
 *       datePattern: Y-m-d
 *
 *       ############################################################
 *       # type が 'size' の場合に指定可能なオプション
 *       ############################################################
 *       # {@link PHI_LogRotateSizeBasedPolicy::setMaxSize()}
 *       maxSize: 1MB
 * </code>
 * <i>その他に指定可能な属性は {@link PHI_LoggerAppender} クラスを参照して下さい。</i>
 *
 * @package logger.appender
 */
class PHI_LoggerFileAppender extends PHI_LoggerAppender
{
  /**
   * @var PHI_LogWriter
   */
  private $_logWriter;

  /**
   * @see PHI_LoggerAppender::__construct()
   */
  public function __construct($appenderId, PHI_ParameterHolder $holder)
  {
    parent::__construct($appenderId, $holder);

    $path = $holder->getString('file', 'phi.log');
    $mode = $holder->getString('mode');
    $rotate = $holder->get('rotate', array());

    if ($rotate->hasName('type')) {
      if ($rotate->getString('type') == 'date') {
        $policy = new PHI_LogRotateDateBasedPolicy($path);

        if ($rotate->hasName('datePattern')) {
          $policy->setDatePattern($rotate->getString('datePattern'));
        }

      } else {
        $policy = new PHI_LogRotateSizeBasedPolicy($path);
        $maxSize = $rotate->get('maxSize');

        if ($maxSize) {
         $policy->setMaxSize($maxSize);
        }
      }

    } else {
      $policy = new PHI_LogRotateNullBasedPolicy($path);
    }

    $generation = $rotate->getInt('generation', 4);
    $policy->setGeneration($generation);

    $appendMode = $rotate->getBoolean('appendMode', TRUE);
    $linefeed = $rotate->getString('linefeed', PHP_EOL);

    $logWriter = new PHI_LogWriter($policy, $appendMode, FALSE);
    $logWriter->setLinefeed($linefeed);

    if ($mode !== NULL) {
      $mode = intval((string) $mode, 8);
      $logWriter->setMode($mode);
    }

    $this->_logWriter = $logWriter;
  }

  /**
   * @see PHI_Logger::write()
   */
  public function write($className, $type, $message)
  {
    if ($this->_logWriter) {
      $message = $this->getLogFormat($className, $type, $message);
      $this->_logWriter->writeLine($message);
    }
  }
}
