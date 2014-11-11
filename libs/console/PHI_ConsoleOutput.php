<?php
/**
 * コンソールに出力を行うクラスです。
 *
 * @package console
 */
class PHI_ConsoleOutput extends PHI_Object
{
  /**
   * @var bool
   */
  private $_silentMode = FALSE;

  /**
   * @var int
   */
  private $_writeIndent = 0;

  /**
   * @var int
   */
  private $_errorIndent = 0;

  /**
   * @var string
   */
  private $_writePrefix;

  /**
   * @var string
   */
  private $_writeSuffix;

  /**
   * @var string
   */
  private $_errorPrefix;

  /**
   * @var string
   */
  private $_errorSuffix;

  /**
   * @var string
   */
  private $_separator = '-';

  /**
   * @var int
   */
  private $_separatorLength = 78;

  /**
   * サイレントモードを設定します。
   * サイレントモードが有効な場合、{@link write()} や {@link error()} で出力した全てのメッセージは非表示となります。
   *
   * @param bool $silentMode TRUE を指定することでサイレントモードが有効となる。デフォルトは FALSE。
   */
  public function setSilentMode($silentMode)
  {
    $this->_silentMode = $silentMode;
  }

  /**
   * {@link error()} や {@link errorLine()} で出力するメッセージのインデントを設定します。
   *
   * @param int $indent インデント数。
   */
  public function setWriteIndent($writeIndent)
  {
    $this->_writeIndent = $writeIndent;
  }

  /**
   * {@link error()} や {@link errorLine()} で出力するメッセージのインデントを設定します。
   *
   * @param int $indent インデント数。
   */
  public function setErrorIndent($errorIndent)
  {
    $this->_errorIndent = $errorIndent;
  }

  /**
   * {@link write()} や {@link writeLine()} で出力するメッセージの書式を設定します。
   *
   * @param string $writePrefix メッセージの接頭辞に付ける文字列。
   * @param string $writePrefix メッセージの接尾辞に付ける文字列。
   */
  public function setWriteFormat($writePrefix, $writeSuffix = NULL)
  {
    $this->_writePrefix = $writePrefix;
    $this->_writeSuffix = $writeSuffix;
  }

  /**
   * {@link error()} や {@link errorLine()} で出力するメッセージの書式を設定します。
   *
   * @param string $writePrefix メッセージの接頭辞に付ける文字列。
   * @param string $writePrefix メッセージの接尾辞に付ける文字列。
   */
  public function setErrorFormat($errorPrefix, $errorSuffix = NULL)
  {
    $this->_errorPrefix = $errorPrefix;
    $this->_errorSuffix = $errorSuffix;
  }

  /**
   * コンソールにメッセージを出力します。
   *
   * @param resource $type メッセージの出力先。STDOUT、または STDERR を指定。
   * @param string $message 出力するメッセージ。
   * @param int $graphicMode メッセージ装飾子。指定可能なオプションは {@link PHI_ANSIGraphic} クラスを参照。
   * @param bool $hasLinefeed 行末に改行コードを含める場合は TRUE を指定。
   */
  private function output($type, $message, $graphicMode, $hasLinefeed)
  {
    if (!$this->_silentMode) {
      // 標準出力メッセージ
      if ($type == STDOUT) {
        if ($this->_writeIndent > 0) {
          $message = str_repeat(' ', $this->_writeIndent) . $message;
        }

        $message = $this->_writePrefix . $message . $this->_writeSuffix;

      // 標準エラーメッセージ
      } else {
        if ($this->_errorIndent > 0) {
          $message = str_repeat(' ', $this->_errorIndent) . $message;
        }

        $message = $this->_errorPrefix . $message . $this->_errorSuffix;
      }

      if ($graphicMode !== NULL) {
        $message = PHI_ANSIGraphic::build($message, $graphicMode);
      }

      if ($hasLinefeed) {
        $message .= PHP_EOL;
      }

      fwrite($type, $message);
    }
  }

  /**
   * 標準エラー (STDERR) にメッセージを出力します。
   * 行末の改行コードを含めたい場合は {@link errorLine()} メソッドを利用して下さい。
   *
   * @param string $message 出力するメッセージ。
   * @param int $graphicMode メッセージ装飾子。詳しくは {@link PHI_ANSIGraphic::build()} メソッドを参照。
   *   デフォルトの装飾は {@link PHI_ANSIGraphic::FOREGROUND_RED}|{@link PHI_ANSIGraphic::ATTRIBUTE_BOLD} となる。
   */
  public function error($message, $graphicMode = NULL)
  {
    if ($graphicMode === NULL) {
      $graphicMode = PHI_ANSIGraphic::FOREGROUND_RED|PHI_ANSIGraphic::ATTRIBUTE_BOLD;
    }

    $this->output(STDERR, $message, $graphicMode, FALSE);
  }

  /**
   * 標準エラー (STDERR) にメッセージを出力します。
   * メッセージの行末は改行が含まれます。
   *
   * @param string $message 出力するメッセージ。
   * @param int $graphicMode メッセージ装飾子。詳しくは {@link PHI_ANSIGraphic::build()} メソッドを参照。
   *   デフォルトの装飾は {@link PHI_ANSIGraphic::FOREGROUND_RED}|{@link PHI_ANSIGraphic::ATTRIBUTE_BOLD} となる。
   */
  public function errorLine($message, $graphicMode = NULL)
  {
    if ($graphicMode === NULL) {
      $graphicMode = PHI_ANSIGraphic::FOREGROUND_RED|PHI_ANSIGraphic::ATTRIBUTE_BOLD;
    }

    $this->output(STDERR, $message, $graphicMode, TRUE);
  }

  /**
   * 標準出力 (STDOUT) にメッセージを出力します。
   * 行末の改行コードを含めたい場合は {@link writeLine()} メソッドを利用して下さい。
   *
   * @param string $message 出力するメッセージ。
   * @param int $graphicMode メッセージ装飾子。詳しくは {@link PHI_ANSIGraphic::build()} メソッドを参照。
   */
  public function write($message, $graphicMode = NULL)
  {
    $this->output(STDOUT, $message, $graphicMode, FALSE);
  }

  /**
   * 標準出力 (STDOUT) にメッセージを出力します。
   * メッセージの行末は改行が含まれます。
   *
   * @param string $message 出力するメッセージ。
   * @param int $graphicMode メッセージ装飾子。詳しくは {@link PHI_ANSIGraphic::build()} メソッドを参照。
   */
  public function writeLine($message, $graphicMode = NULL)
  {
    $this->output(STDOUT, $message, $graphicMode, TRUE);
  }

  /**
   * 標準出力 (STDOUT) に改行を出力します。
   *
   * @param int $blankLines 改行数。
   */
  public function writeBlankLines($blankLines = 1)
  {
    fwrite(STDOUT, str_repeat(PHP_EOL, $blankLines));
  }

  /**
   * {@link writeSeparator() 区切り線} のスタイルを設定します。
   *
   * @param string $separator 区切り線に使用する文字。デフォルトは '-' (ハイフン)。
   * @param int $length 区切り線の長さ。デフォルトは 78。
   */
  public function setWriteSeparatorStyle($separator, $length = 78)
  {
    $this->_separator = $separator;
    $this->_separatorLength = $length;
  }

  /**
   * 区切り線を取得します。
   *
   * @return string 区切り線を返します。行末は改行コードを含みます。
   * @see setWriteSeparatorStyle()
   */
  public function getSeparator()
  {
    return str_repeat($this->_separator, $this->_separatorLength) . PHP_EOL;
  }

  /**
   * 標準出力 (STDOUT) に区切り線を出力します。
   *
   * @see setWriteSeparatorStyle()
   * @see getSeparator()
   */
  public function writeSeparator()
  {
    fwrite(STDOUT, $this->getSeparator());
  }
}
