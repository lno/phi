<?php
/**
 * CSV 形式でファイルにデータを書き込みます。
 *
 * @link http://www.ietf.org/rfc/rfc4180.txt Common Format and MIME Type for Comma-Separated Values (CSV) Files
 * @package util.file.csv
 */
class PHI_CSVWriter extends PHI_FileWriter
{
  /**
   * 各フィールドをダブルクォートで囲むかどうか。
   * @var bool
   */
  private $_fieldEnclosedWithQuote = FALSE;

  /**
   * フィールドを区切るセパレータ。
   * @var string
   */
  private $_separator = ',';

  /**
   * ヘッダが含まれているかどうか。
   * @var string
   */
  private $_header = 'absent';

  /**
   * フィールドサイズ。
   * @var int
   */
  private $_fieldSize;

  /**
   * コンストラクタ。
   *
   * @see PHI_FileWriter::__construct()
   */
  public function __construct($path = NULL)
  {
    parent::__construct($path, FALSE);

    $this->_linefeed = "\r\n";
  }

  /**
   * レコードの各フィールドをダブルクォーテーションで囲むかどうか設定します。
   *
   * @param bool $fieldEnclosedWithQuote 各フィールドをダブルクォートで囲む場合は TRUE を指定。既定値は FALSE。
   */
  public function setFieldEnclosedWithQuote($fieldEnclosedWithQuote)
  {
    $this->_fieldEnclosedWithQuote = $fieldEnclosedWithQuote;
  }

  /**
   * フィールドを区切るセパレータを設定します。
   *
   * @param string $separator セパレータ文字。既定値はカンマが使用される。
   */
  public function setSeparator($separator)
  {
    $this->_separator = $separator;
  }

  /**
   * CSV の行を生成します。
   *
   * @param array $fields フィールド配列。
   * @return string CSV 形式のレコードを返します。
   */
  private function buildRecord(array $fields)
  {
    $buffer = NULL;

    if (is_array($fields)) {
      foreach ($fields as $field) {
        if ($this->_fieldEnclosedWithQuote) {
          // 文字列中に含まれる全てのダブルクォートをエスケープ
          $offset = 0;

          while (($pos = mb_strpos($field, '"', $offset, $this->_inputEncoding)) !== FALSE) {
            $field = PHI_StringUtils::insert($field, '"', $pos, NULL, $this->_inputEncoding);
            $offset = $pos + 2;
          }

          $field = '"' . $field . '"';

        } else {
          $pattern = '/[' . $this->_linefeed . '",]/';

          if (preg_match($pattern, $field)) {
            $field = '"' . $field . '"';
          }
        }

        $buffer .= $field . $this->_separator;
      }
    }

    $buffer = rtrim($buffer, $this->_separator) . $this->_linefeed;

    return $buffer;
  }

  /**
   * CSV の見出し行を設定します。
   *
   * @param array $fields 見出しリスト。
   * @throws PHI_ParseException 見出し列数がレコード列数とマッチしない場合に発生。
   */
  public function setHeader(array $fields)
  {
    $size = sizeof($fields);

    if ($this->_fieldSize === NULL) {
      $this->_fieldSize = $size;

    } else if ($this->_fieldSize != $size) {
      $message = sprintf('Invalid number of columns. [%s]', implode(',', $fields));
      throw new PHI_ParseException($message);
    }

    $this->_writeBuffer .= $this->buildRecord($fields);
    $this->_header = 'present';
  }

  /**
   * CSV レコードを追加します。
   *
   * @param array $fields フィールドの配列。
   * @throws PHI_ParseException フィールド数が見出し列数、または他の行の列数とマッチしない場合に発生。
   */
  public function addRecord(array $fields)
  {
    $size = sizeof($fields);

    if ($this->_fieldSize === NULL) {
      $this->_fieldSize = $size;

    } else if ($this->_fieldSize != $size) {
      $message = sprintf('Invalid number of columns. [%s]', implode(',', $fields));
      throw new PHI_ParseException($message);
    }

    $this->_writeBuffer .= $this->buildRecord($fields);
  }

  /**
   * 連想配列のデータを CSV に追加します。
   *
   * @param array $records 連想配列形式のデータ。array(array(1, 'foo'), array(2, 'bar')) といった形式を指定します。
   */
  public function addRecords(array $records)
  {
    foreach ($records as $record) {
      $this->addRecord($record);
    }
  }

  /**
   * 生成した CSV データをファイルに出力します。
   */
  public function writeCSV()
  {
    if (!$this->_lazyWrite) {
      $this->flush();
    }
  }

  /**
   * CSV データをダウンロードします。
   * download() メソッドはファイルをダウンロードするのに必要な HTTP ヘッダを自動的にクライアントへ送信します。
   *
   * @param string $fileName ダウンロード時のファイル名。未指定の場合はアクション名がファイル名として使用される。
   */
  public function download($fileName = NULL)
  {
    $controller = PHI_FrontController::getInstance()->getRequest();

    if ($fileName === NULL) {
      $route = $controller->getRequest()->getRoute();
      $fileName = $route->getForwardStack()->getLast()->getAction()->getActionName() . '.csv';
    }

    $contentType = sprintf('text/csv; charset=%s; header=%s',
      $this->_outputEncoding,
      $this->_header);

    $response = $controller->getResponse();
    $response->setContentType($contentType);
    $response->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
    $response->write($this->buildOutputData());

    $this->clear();
  }

  /**
   * @see PHI_FileWriter::__destruct()
   */
  public function __destruct()
  {
    if ($this->_path !== NULL) {
      parent::__destruct();
    }
  }
}
