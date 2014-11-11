<?php
/**
 * メールメッセージを解析します。
 *
 * このクラスは実験的なステータスにあります。
 * これは、このクラスの動作、メソッド名、ここで書かれていること全てが phi の将来のバージョンで予告なく変更される可能性があることを意味します。
 * 注意を喚起するとともに自分のリスクでこのクラスを使用して下さい。
 *
 * @package net.mail
 */
class PHI_MailParser extends PHI_Object
{
  /**
   * 'text/plain' 定数。
   */
  const CONTENT_TYPE_TEXT_PLAIN = 'text/plain';

  /**
   * 'text/html' 定数。
   */
  const CONTENT_TYPE_TEXT_HTML ='text/html';

  /**
   * 'multipart/mixed' 定数。
   */
  const CONTENT_TYPE_MULTIPART_MIXED = 'multipart/mixed';

  /**
   * 'multipart/alternative' 定数。
   */
  const CONTENT_TYPE_MULTIPART_ALTERNATIVE = 'mutipart/alternative';

  /**
   * 'multipart/report' 定数。
   */
  const CONTENT_TYPE_MULTIPART_REPORT = 'multipart/report';

  /**
   * 'multipart/relate' 定数。
   */
  const CONTENT_TYPE_MULTIPART_RELATED = 'multipart/related';

  /**
   * 'message/rfc822' 定数。
   */
  const CONTENT_TYPE_RFC822 = 'message/rfc822';

  /**
   * メッセージ全体を解析。
   */
  const PARSE_MODE_ALL = 1;

  /**
   * メッセージ内ヘッダのみ解析。
   * マルチパートメッセージにおける各パートの MIME パートヘッダは解析対象外。
   */
  const PARSE_MODE_HEADER_ONLY = 2;

  /**
   * オリジナルヘッダ。
   * @var string
   */
  private $_originalHeaders;

  /**
   * オリジナルメッセージ本文。
   * @var string
   */
  private $_originalBody;

  /**
   * オリジナルメールデータ。
   * @var string
   */
  private $_data;

  /**
   * {@link PHI_MailMessage} オブジェクト。
   * @var PHI_MailMessage
   */
  private $_message;

  /**
   * コンストラクタ。
   *
   * @param string $message メール本体のメッセージファイル。未指定の場合は標準入力から読み込まれます。
   */
  public function __construct($message = NULL)
  {
    if ($message === NULL) {
      $this->_data = file_get_contents('php://stdin');

    } else {
      $this->_data = $message;
    }
  }

  /**
   * メッセージを解析します。
   *
   * @param int $parseMode メッセージの解析モードを指定。
   * @throws PHI_ParseException メッセージの解析が失敗した際に発生。
   */
  public function parse($parseMode = self::PARSE_MODE_ALL)
  {
    $this->_message = new PHI_MailMessage($this->_data);

    if ($parseMode == self::PARSE_MODE_ALL) {
      $this->parseMessage($this->_message, $this->_data);

    } else {
      $header = substr($this->_data, 0, strpos($this->_data, "\n\n"));

      $this->_originalHeaders = $header;
      $this->parseHeader($this->_message, $header);
    }
  }

  /**
   * メッセージを解析します。
   * MIME 構成の場合は全てのパートを (ネストを含め) 再帰的に解析します。
   *
   * @param PHI_MailPart $parentPart 親パート。
   * @param string $partMessage パートメッセージ。
   * @throws PHI_ParseException メールテキストの形式が不正な場合に発生。
   */
  private function parseMessage(PHI_MailPart $parentPart, $partMessage)
  {
    $pos = strpos($partMessage, "\n\n");
    $header = substr($partMessage, 0, $pos);

    if ($pos === FALSE) {
      $message = 'Mail header format is invalid.';
      throw new PHI_ParseException($message);
    }

    if ($this->_originalHeaders === NULL) {
      $this->_originalHeaders = $header;
    }

    if ($pos !== FALSE) {
      $body = substr($partMessage, $pos + 2);

      $this->parseHeader($parentPart, $header);
      $this->parseBody($parentPart, $body);

    } else {
      // RFC822 の場合、本文パートが存在しない場合がある
      $this->parseHeader($parentPart, $header);
    }
  }

  /**
   * {@link parse()}、{@link parseHeaderOnly()} メソッドにより解析されたメッセージオブジェクトを取得します。
   *
   * @return PHI_MailMessage PHI_MailMessage のインスタンスを返します。
   */
  public function getMailMessage()
  {
    return $this->_message;
  }

  /**
   * メッセージヘッダを解析します。
   *
   * @param PHI_MailPart $parentPart 親パート。
   * @param string $header ヘッダ文字列。
   */
  private function parseHeader(PHI_MailPart $parentPart, $headers)
  {
    $splitHeader = preg_split('/(?!\n\s)\n/', $headers);
    $headers = array();

    foreach ($splitHeader as $header) {
      if (preg_match('/^([\w\-]+):(.+)$/', $header, $matches, PREG_OFFSET_CAPTURE)) {
        $name = strtolower($matches[1][0]);
        $value = trim($matches[2][0]);

        // 同名のヘッダが複数存在する場合は全ての値を配列形式で保存
        if (isset($headers[$name])) {
          if (!is_array($headers[$name])) {
            $tempValue = $headers[$name];

            $headers[$name] = array();
            $headers[$name][] = $tempValue;
          }

          $headers[$name][] = $value;

        } else {
          $headers[$name] = $value;
        }
      }
    }

    $parentPart->setHeaders($headers);

    $this->parseContentType($parentPart);
  }

  /**
   * 解析するコンテンツメッセージを取得します。
   *
   * @return string コンテンツメッセージを返します。
   */
  public function getOriginalContents()
  {
    return $this->_data;
  }

  /**
   * オリジナルのヘッダフィールド文字列を取得します。
   *
   * @return string オリジナルのヘッダフィールド文字列を返します。
   */
  public function getOriginalHeaders()
  {
    return $this->_originalHeaders;
  }

  /**
   * オリジナルのメッセージ本文を取得します。
   *
   * @return string オリジナルのメッセージ本文を返します。
   */
  public function getOriginalBody()
  {
    return $this->_originalBody;
  }

  /**
   * メッセージ本文を解析します。
   *
   * @param PHI_MailPart $parentPart 親パート。
   * @param string $body メッセージ本文。
   */
  private function parseBody(PHI_MailPart $parentPart, $body)
  {
    $contentType = $parentPart->getContentType();

    // マルチパートの解析
    if (stripos($contentType, 'multipart/') !== FALSE) {
      $this->parsePart($parentPart, $body);

    // 転送メールの解析
    } else if ($contentType == self::CONTENT_TYPE_RFC822) {
      $part = new PHI_MailPart();
      $this->parseMessage($part, $body);

      if ($part->hasPart()) {
        $subParts = $part->getParts();

        foreach ($subParts as $subPart) {
          $parentPart->addPart($subPart);
        }

      } else {
        $parentPart->setBody($part->getBody());
      }

    } else {
      $parentPart->setBody($this->decodeBody($parentPart, $body));

      if (stripos($contentType, 'text/') === FALSE) {
        $this->parseAttachment($parentPart);
      }
    }

    $this->_originalBody = $body;
  }

  /**
   * メッセージ本文をデコードします。
   *
   * @param PHI_MailPart $parentPart 親パート。
   * @param string $body 解析対象のメッセージ本文。
   * @return string デコードメッセージを返します。
   */
  private function decodeBody(PHI_MailPart $parentPart, $body)
  {
    $contentTransferEncoding = $parentPart->getHeaderValue('content-transfer-encoding');

    if ($contentTransferEncoding == 'quoted-printable') {
      $decodeBody = quoted_printable_decode($body);

    } else {
      $toEncode = PHI_Config::getApplication()->get('charset.default');
      $fromEncode = $parentPart->getCharset();

      try {
        $decodeBody = mb_convert_encoding($body, $toEncode, $fromEncode);
      } catch (ErrorException $e) {
        $decodeBody = mb_convert_encoding($body, $toEncode, 'ASCII');
      }
    }

    return $decodeBody;
  }

  /**
   * 添付データを解析します。
   *
   * @param PHI_MailPart $parentPart 親パート。
   */
  private function parseAttachment(PHI_MailPart $part)
  {
    $name = $part->getContentTypeParameter('name');
    $contentDisposition = $part->getHeaderValue('content-disposition');

    // 一部のキャリアは Content-Disposition フィールドが追加されないので、ContentType name 属性も
    // 添付ありの基準対象とする (RFC 違反)
    if (strlen($name) || isset($contentDisposition)) {
      $contentTransferEncoding = $part->getHeaderValue('content-transfer-encoding');
      $attachment = new PHI_MailAttachment();

      if (strcasecmp($contentTransferEncoding, 'base64') == 0) {
        $data = base64_decode($part->getBody());
      } else {
        $data = $part->getBody();
      }

      $attachment->setAttachment($data);
      $fileName = $this->decodeAttachmentFileName($part, $attachment, $contentDisposition);

      $attachment->setMimeType($part->getContentType());
      $attachment->setFileName($fileName);
      $attachment->setFileSize(strlen($data));
      $attachment->setHeaders($part->getHeaders());

      $this->_message->addAttachmentFile($attachment);
    }
  }

  /**
   * 添付データのファイル名を解析します。
   *
   * @param PHI_MailPart $parentPart 親パート。
   * @param PHI_MailAttachment $attachment 添付パート。
   * @param string $contentDisposition Content-Disposition フィールド。
   * @param string $contentDisposition 添付データのファイル名を返します。
   */
  private function decodeAttachmentFileName(PHI_MailPart $part, PHI_MailAttachment $attachment, $contentDisposition)
  {
    $contentDispositions = explode(";", $contentDisposition, 2);

    // Parse attachment type (inline or attachment)
    if (strcasecmp($contentDispositions[0], 'attachment') == 0) {
      $attachment->setDispositionType(PHI_MailAttachment::DISPOSITION_TYPE_ATTACHMENT);
    }

    $strict = TRUE;
    $tempFileName = trim($contentDispositions[1]);

    // Content-Disposition フィールドにファイル名が含まれていない場合 (古いタイプのフィールド書式)
    if (strlen($tempFileName) == 0) {
      $tempFileName = $part->getContentTypeParameter('name');
      $strict = FALSE;
    }

    // BASE64 形式
    $encoding = PHI_Config::getApplication()->get('charset.default');

    if (($pos = strpos($tempFileName, '"')) !== FALSE) {
      $parameters = explode('?', $tempFileName);
      $array = explode("\n", $tempFileName);

      $fileName = NULL;

      if (sizeof($parameters) > 1) {
        foreach ($array as $value) {
          $separate = explode('?', $value);
          $tempFileName = $separate[3];

          if ($separate[2] == 'B') {
            $tempFileName = base64_decode($tempFileName);
          } else if ($separate[2] == 'Q') {
            $tempFileName = quoted_printable_decode($tempFileName);
          }

          try {
            $fileName .= mb_convert_encoding($tempFileName, $encoding, $separate[1]);
          } catch (ErrorException $e) {
            $fileName .= mb_convert_encoding($tempFileName, $encoding, 'ASCII');
          }
        }

      } else {
        foreach ($array as $value) {
          $start = strpos($value, '"');
          $end = strrpos($value, '"');

          $fileName .= substr($value, $start + 1, $end - $start - 1);
        }
      }

    // US-ASCII 以外の文字を含む場合 (RFC2231)
    } else if ($strict) {
      preg_match('/filename[^=]*=([^\']*)\'[^\']*\'(.+)/', $tempFileName, $matches, PREG_OFFSET_CAPTURE);

      $charset = $matches[1][0];
      $fileName = rtrim($matches[2][0], ';');

      $array = explode("\n", substr($tempFileName, strlen($fileName) + $matches[2][1] + 1));

      foreach ($array as $value) {
        $fileName .= trim(substr($value, strpos($value, '=') + 1), ';');
      }

      $fileName = urldecode($fileName);

      try {
        $fileName = mb_convert_encoding($fileName, $encoding, $charset);
      } catch (ErrorException $e) {
        $fileName = mb_convert_encoding($fileName, $encoding, 'ASCII');
      }

    } else {
      $fileName = $tempFileName;
    }

    return $fileName;
  }

  /**
   * MIME パートを解析します。
   *
   * @param PHI_MailPart $parentPart 親パート。
   * @param string $body 解析対象の本文パート。
   * @throws PHI_ParseException 対象パートで MIME バウンダリが見つからない場合に発生。
   */
  private function parsePart(PHI_MailPart $parentPart, $body)
  {
    $boundary = $parentPart->getContentTypeParameter('boundary');

    if ($boundary) {
      $boundary = '--' . $boundary;
      $length = strlen($boundary) + 1;

      // This is a multi-part message in MIME format
      $pos = strpos($body, $boundary);

      $parentPart->setBody(substr($body, 0, $pos));

      $start = $pos + $length;
      $end = strpos($body, $boundary . '--');

      while ($pos = strpos(substr($body, $start, $end), $boundary)) {
        $partMessage = substr($body, $start, $pos);
        $start = $start + $pos + $length;

        $part = new PHI_MailPart();
        $this->parseMessage($part, $partMessage);

        $parentPart->addPart($part);
      }

    } else {
      throw new PHI_ParseException('MIME Boundary is not found.');
    }
  }

  public function parseContentType(PHI_MailPart $parentPart)
  {
    $headers = $parentPart->getHeaders();
    $contentType = NULL;

    if (isset($headers['content-type'])) {
      if (is_array($headers['content-type'])) {
        $contentType = PHI_ArrayUtils::lastValue($headers['content-type']);
      } else {
        $contentType =  $headers['content-type'];
      }
    }

    if (strlen($contentType)) {
      preg_match_all('/([^;=\s]+)(?:="?([^";]*)"?)?/', $contentType, $matches);

      $contentType = $matches[1][0];

      $j = sizeof($matches[1]);
      $parameters = array();

      for ($i = 1; $i < $j; $i++) {
        $name = $matches[1][$i];
        $value = $matches[2][$i];

        if (!isset($parameters[$name])) {
          $parameters[$name] = $value;
        }
      }

      if (isset($parameters['charset'])) {
        $parentPart->setCharset($parameters['charset']);
      }

    } else {
      $contentType = self::CONTENT_TYPE_TEXT_PLAIN;
    }

    $parentPart->setContentType($contentType, $parameters);
  }

  public static function decodeHeader(PHI_MailPart $parentPart, $name, $index = NULL)
  {
    $headerValues = $parentPart->getHeaderValues($name);

    if ($headerValues) {
      if (is_array($headerValues)) {
        if ($index === NULL) {
          $decodeHeaderValues = array();

          foreach ($headerValues as $headerValue) {
            $decodeHeaderValues[] = self::parseDecodeHeader($headerValue);
          }

          return $decodeHeaderValues;

        } else {
          return PHI_ArrayUtils::find($headerValues, $index);
        }

      } else {
        $decodeHeaderValue = self::parseDecodeHeader($headerValues);

        return $decodeHeaderValue;
      }
    }

    return NULL;
  }

  private static function parseDecodeHeader($headerValue)
  {
    $search = array("\t", "\n", "\r", "\0", "\0B");
    $headerValue = str_replace($search, ' ', $headerValue);

    $splits = PHI_StringUtils::splitExclude($headerValue, ' ', '"', FALSE);

    $encode = PHI_Config::getApplication()->get('charset.default');
    $buffer = NULL;
    $insertSpace = FALSE;

    foreach ($splits as $split) {
      if (preg_match('/^=\?(.*)\?(.*)\?(.*)\?=/', $split, $matches)) {
        $charset = strtoupper($matches[1]);
        $encodeType = strtoupper($matches[2]);

        if (!$insertSpace) {
          $buffer .= ' ';
        }

        if ($encodeType == 'B') {
          $decode = base64_decode($matches[3]);

          try {
            $buffer .= mb_convert_encoding($decode, $encode, $charset);

          } catch (ErrorException $e) {
            $buffer .= mb_convert_encoding($decode, $encode, 'ASCII');
          }

        } else if ($encodeType == 'Q') {
          try {
            $decode = quoted_printable_decode($matches[3]);
            $buffer .= mb_convert_encoding($decode, $encode, $charset);

          } catch (ErrorException $e) {
            $buffer .= mb_convert_encoding($decode, $encode, 'ASCII');
          }

        } else {
          $buffer .= $split;
        }

        $insertSpace = TRUE;

      } else if (strlen($split)) {
        $buffer .= ' ' . $split;
        $insertSpace = FALSE;
      }
    }

    return trim($buffer);
  }
}
