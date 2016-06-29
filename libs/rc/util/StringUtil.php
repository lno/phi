<?php
/**
 * @package rc.util
 */
Class StringUtil
{
  public static function get($string, $alt = '')
  {
    return StringUtil::isEmpty($string) ? $alt : $string;
  }

  public static function nval($string, $alt = '')
  {
    return StringUtil::get($string, $alt);
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
   * @param string|array $search
   * @return bool
   */
  public static function has($string, $search, $orSearch = TRUE)
  {
    if (is_array($search)) {
      if ($orSearch) {
        $result = FALSE;
        foreach ($search as $word) {
          if (StringUtil::has($string, $word, $orSearch)) {
            $result = TRUE;
            break;
          }
        }
      } else {
        $result = TRUE;
        foreach ($search as $word) {
          if (!StringUtil::has($string, $word, $orSearch)) {
            $result = FALSE;
            break;
          }
        }
      }
      return $result;
    } else {
      // search を文字列として処理
      return strpos($string, $search) !== FALSE;
    }
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
      if (self::has($string, $searchWord)) {
        $hasString = TRUE;
        break;
      }
    }

    return $hasString;
  }

  public static function split($string, $delimiter = NULL)
  {
    if ($delimiter === NULL) {
      return str_split($string);
    } else {
      return explode($delimiter, $string);
    }
  }

  public static function decodeJson($string, $assoc = FALSE)
  {
    $string = htmlspecialchars_decode($string);
    return json_decode($string, $assoc);
  }

  public static function trim($string, $trimPattern = NULL)
  {
    if ($trimPattern === NULL) {
      return trim($string);
    } else {
      return trim(preg_replace($trimPattern, '', $string));
    }
  }

  public static function hasMultiByteString($string)
  {
    return (strlen($string) !== mb_strlen($string,'utf8'));
  }

  public static function escapePregStatement($string)
  {
    $search = ['\\', '/', '^', '.', '+', '$', '*', '?', '|', '(', ')', '[', ']', '{', '}'];
    $replace = ['\\\\', '\/', '\^', '\.','\+',  '\$', '\*', '\?', '\|', '\(', '\)', '\[', '\]', '\{', '\}'];

    return str_replace($search, $replace, $string);
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
}
