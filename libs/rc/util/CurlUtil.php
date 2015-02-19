<?php
/**
 * @package rc.util
 */
class CurlUtil
{
  public static function fileGetContents($path, $user = NULL, $password = NULL)
  {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $path);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    if ($user && $password) {
      curl_setopt($curl, CURLOPT_USERPWD, "$user:$password");
    }
    $result = curl_exec($curl);
    curl_close($curl);

    return $result;
  }

  public static function fileGetContentsBySftp($path, $host, $user = NULL, $password = NULL)
  {
    $filePath = "sftp://$host$path";

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $filePath);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    if ($user && $password) {
      curl_setopt($curl, CURLOPT_USERPWD, "$user:$password");
    }
    $result = curl_exec($curl);
    curl_close($curl);

    return $result;
  }
}