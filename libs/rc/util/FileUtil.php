<?php
/**
 * @package rc.util
 */
class FileUtil
{
  /**
   * 指定したディレクトリに存在するファイルの数を返します。（ディレクトリの数を含む）
   *
   * @param $dirPath
   * @return int|null
   */
  public static function fileCount($dirPath)
  {
    $fileCount = NULL;

    if (is_dir($dirPath)) {
      $dirPattern = sprintf('%s%s*',
        $dirPath,
        DIRECTORY_SEPARATOR
      );
      $dirFileList = glob($dirPattern);
      $fileCount = count($dirFileList);
    }

    return $fileCount;
  }

  public static function getFileName($filePath)
  {
    return substr(strrchr($filePath, '/'), 1);
  }

  public static function downloadSftpFile($savePath, $ftpFilePath, $host, $user, $password)
  {
    $fileContents = CurlUtil::fileGetContentsBySftp(
      $ftpFilePath,
      $host,
      $user,
      $password
    );
    $writer = new PHI_FileWriter($savePath, FALSE);
    $writer->write($fileContents);
    $writer->close();
  }

  /**
   * @param $filePath
   * @return string
   */
  public static function zip($filePath)
  {
    $resultZipFileName = NULL;

    // 現在ディレクトリの変更
    $curDir = getcwd();
    chdir(dirname($filePath));

    // 主処理の実行
    $fileName = basename($filePath);
    list($zipFileName) = explode(".", $fileName);
    $zipFileName = $zipFileName.'.zip';
    $zip = new ZipArchive();
    $res = $zip->open('.'.DIRECTORY_SEPARATOR.$zipFileName, ZipArchive::CREATE);
    if ($res === TRUE) {
      $zip->addFile('.'.DIRECTORY_SEPARATOR.$fileName);
      $zip->close();
      PHI_FileUtils::deleteFile($filePath);
      $resultZipFileName = $zipFileName;
    }

    // 現在ディレクトリを元に戻す
    chdir($curDir);

    return $resultZipFileName;
  }
}
