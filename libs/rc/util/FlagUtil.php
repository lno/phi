<?php
/**
 * @package rc.util
 */
class FlagUtil
{
  public static function enabled($flag)
  {
    return !FlagUtil::disabled($flag);
  }

  public static function disabled($flag)
  {
    return ($flag === 0);
  }
}