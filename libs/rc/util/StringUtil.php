<?php
Class StringUtil
{
  public static function nvl($string, $alt = '')
  {
    return ($string === NULL) ? $alt : $string;
  }

  /**
   * @param $string
   * @return bool
   */
  public static function isEmpty($string)
  {
    return ($string === NULL || $string === '');
  }

  /**
   * @param $string
   * @return bool
   */
  public static function exists($string)
  {
    return !StringUtil::isEmpty($string);
  }

  /**
   * @param string $string
   * @param string $searchWord
   * @return bool
   */
  public static function hasString($string, $searchWord)
  {
    return strpos($string, $searchWord) !== FALSE;
  }

  /**
   * @param $string
   * @return bool|string
   */
  public static function formatDateW3C($string)
  {
    return date('Y-m-d\TH:i:sP', strtotime($string));
  }

  /**
   * @param $string
   * @return bool|string
   */
  public static function formatRssPubDate($string)
  {
    return date('D, d M Y H:i:s O', strtotime($string));
  }

  /**
   * @param $string
   * @param array $searchWords
   * @return bool
   */
  public static function hasAnyString($string, array $searchWords)
  {
    $hasString = FALSE;

    foreach ($searchWords as $searchWord) {
      if (self::hasString($string, $searchWord)) {
        $hasString = TRUE;
        break;
      }
    }

    return $hasString;
  }

  public static function toDate($string, $format = 'Y-m-d')
  {
    return date($format, strtotime($string));
  }

  public static function getEncoding($string)
  {
    return mb_detect_encoding($string, 'UTF-8,ASCII,JIS,EUC-JP,SJIS,CP51932,SJIS-WIN');
  }

  public static function mbConvertEncoding($string)
  {
    mb_internal_encoding('UTF-8');
    $encodeString = mb_convert_encoding($string, 'UTF-8', StringUtil::getEncoding($string));

    return $encodeString;
  }

  public static function split($string, $delimiter = NULL)
  {
    if ($delimiter === NULL) {
      return str_split($string);
    } else {
      return explode($delimiter, $string);
    }
  }

  public static function jsonDecode($string, $assoc = FALSE)
  {
    $string = htmlspecialchars_decode($string);
    return json_decode($string, $assoc);
  }

  public static function pregTrim($string, $trimPattern)
  {
    return trim(preg_replace($trimPattern, '', $string));
  }

  public static function hasJapaneseString($string)
  {
    return (strlen($string) !== mb_strlen($string,'utf8'));
  }

  /**
   * @param $string
   * @param $pattern
   * @return bool
   */
  public static function match($string, $pattern)
  {
    return preg_match($pattern, $string) === 1;
  }
}
