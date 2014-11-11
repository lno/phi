<?php
/**
 * データベースサーバへ送信されたクエリをロギングします。
 *
 * global_filters.yml の設定例:
 * <code>
 * {フィルタ ID}:
 *   # フィルタクラス名。
 *   class: PHI_SQLLoggingFilter
 *
 *   # ログの送信先となるログアペンダ ID を指定。({@link PHI_LoggerAppender} クラスを参照)
 *   appenderId:
 *
 *   # ログレベルの指定。(オプション)
 *   level: <?php echo {@link PHI_Logger::LOGGER_MASK_TRACE} ?>
 * </code>
 *
 * application.yml の設定例:
 * <code>
 * # ログアペンダの定義。
 * logger:
 *   traceAppender:
 *     class: {@link PHI_LoggerFileAppender}
 *     mask: <?php echo {@link PHI_Logger::LOGGER_MASK_TRACE} ?>
 *     file: trace.log
 *     rotate:
 *       type: date
 *       datePattern: Y-m
 * </code>
 *
 * 例えば次のようなログが出力されます。
 * <code>
 * {logs/trace.log}
 * 2013/04/12 03:30:40 TRACE [PHI_SQLLoggingFilter] - "SELECT * FROM members WHERE delete_flag = 0 ORDER BY member_id DESC LIMIT 10 OFFSET 0" ({module_name}/{action_name} {invoke_class}::{invoke_method})
 * </code>
 * ログの書式を変更したい場合は、{@link getLogFormat()} をオーバーライドした拡張クラスを作成して下さい。
 *
 * @package controller.filter
 */
class PHI_SQLLoggingFilter extends PHI_Filter
{
  /**
   * @throws PHI_ConfigurationException 必須属性が未定義の場合に発生。
   * @see PHI_Filter::doFilter()
   */
  public function doFilter(PHI_FilterChain $chain)
  {
    // プロファイラの実行
    $profiler = $this->getDatabase()->getProfiler();
    $profiler->start();

    $chain->filterChain();

    $profiler->stop();
    $reports = $profiler->getReports();

    // 実行された SQL をロギング
    $appenderId = $this->_holder->get('appenderId');

    if ($appenderId === NULL) {
      $message = sprintf('\'appenderId\' attribute is undefined. [%s]', $this->_filterId);
      throw new PHI_ConfigurationException($message);
    }

    $level = $this->_holder->getInt('level', PHI_Logger::LOGGER_MASK_TRACE);
    $appender = PHI_Config::getApplication()->get('logger')->get($appenderId);

    if ($appender === NULL) {
      $message = sprintf('\'%s\' appender is undefined in application.yml.', $appenderId);
      throw new PHI_ConfigurationException($message);
    }

    $logger = PHI_Logger::getLogger(get_class(), FALSE);
    $logger->addAppender($appenderId, $appender);

    foreach ($reports as $report) {
      $message = $this->getLogFormat($report);
      call_user_func_array(array($logger, 'send'), array($level, $message));
    }
  }

  /**
   * ロガーに送信するメッセージを書式化します。
   *
   * @param PHI_SQLProfilerReport $report PHI_SQLProfilerReport オブジェクト。
   * @return string 書式化したメッセージ文字列を返します。
   */
  protected function getLogFormat(PHI_SQLProfilerReport $report)
  {
    $message = sprintf('"%s" (%s/%s %s::%s())',
      $report->statement,
      $report->moduleName,
      $report->actionName,
      $report->className,
      $report->methodName);

    return $message;
  }
}
