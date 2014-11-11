<?php
/**
 * ファイルアップロード機能を提供するユーティリティです。
 *
 * @package util
 */
class PHI_FileUploader extends PHI_Object
{
  /**
   * アップロードされたファイルの情報。
   * @var array
   */
  private $_fileInfo;

  /**
   * コンストラクタ。
   *
   * @param string $name ファイルのフィールド名。'.' (ピリオド) を含む名前は連想配列名として扱われる。
   * @throws PHI_DataNotFoundException アップロードファイルが見つからない場合に発生。
   */
  public function __construct($name)
  {
    $fileInfo = self::getFileInfo($name);

    if ($fileInfo !== NULL) {
      $this->_fileInfo = $fileInfo;

    } else {
      $message = sprintf('File is not up-loaded. [%s]', $name);
      throw new PHI_DataNotFoundException($message);
    }
  }

  /**
   * ファイルが正常にアップロードされているかどうかチェックします。
   *
   * @return bool ファイルが正常にアップロードされている場合は TRUE を返します。
   */
  public function isUpload()
  {
    if ($this->_fileInfo['error'] === UPLOAD_ERR_OK) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * ファイルがアップロードされているかどうかチェックします。
   * {@link isUpload()} メソッドと異なり、アップロードの成功可否はチェックしません。
   *
   * @param string $name チェック対象のフィールド名。
   * @return bool ファイルがアップロードされている場合は TRUE を返します。
   */
  public static function hasUpload($name)
  {
    $fileInfo = self::getFileInfo($name);

    if ($fileInfo === NULL || $fileInfo['error'] == UPLOAD_ERR_NO_FILE) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * アップロードされたファイルの名前を取得します。
   *
   * @param bool $extension ファイル名に拡張子を含める場合は TRUE を指定。
   * @return string アップロードされたファイルの名前を返します。
   */
  public function getFileName($extension = TRUE)
  {
    $fileName = $this->_fileInfo['name'];

    if (!$extension && ($pos = strpos($fileName, '.')) !== FALSE) {
      $fileName = substr($fileName, 0, $pos);
    }

    return $fileName;
  }

  /**
   * アップロードされたファイルの拡張子を取得します。
   *
   * @return string アップロードされたファイルの拡張子を返します。ファイルに拡張子が含まれない場合は NULL を返します。
   * @deprecated {@link getFileExtension()} に置き換えて下さい。1.15.0 で破棄されます。
   */
  public function getFileSuffix()
  {
    $fileName = $this->_fileInfo['name'];

    if (($pos = strpos($fileName, '.')) !== FALSE) {
      return substr($fileName, $pos);
    }

    return NULL;
  }

  /**
   * アップロードされたファイルの拡張子を取得します。
   *
   * @return string アップロードされたファイルの拡張子を返します。ファイルに拡張子が含まれない場合は NULL を返します。
   */
  public function getFileExtension()
  {
    $fileName = $this->_fileInfo['name'];

    if (($pos = strpos($fileName, '.')) !== FALSE) {
      return substr($fileName, $pos);
    }

    return NULL;
  }

  /**
   * アップロードされたファイルの一時ファイル名を取得します。
   *
   * @return string アップロードされたファイルの一時ファイル名を返します。
   */
  public function getTemporaryFilePath()
  {
    return $this->_fileInfo['tmp_name'];
  }

  /**
   * アップロードされたファイルの MIME タイプを取得します。
   * MIME タイプはブラウザによって同一ファイルに対しても異なる値を返す可能性がある点に注意して下さい。
   *
   * @return string アップロードされたファイルの MIME タイプを返します。
   */
  public function getMIMEType()
  {
    return $this->_fileInfo['type'];
  }

  /**
   * アップロードされたファイルのステータスコードを取得します。
   *
   * @return int ステータスコード値として UPLOAD_ERR_* 定数を返します。
   * @link http://www.php.net/manual/features.file-upload.errors.php Error Messages Explained
   */
  public function getStatus()
  {
    return $this->_fileInfo['error'];
  }

  /**
   * アップロードされたファイルの容量を取得します。
   *
   * @return string アップロードされたファイルの容量を返します。
   */
  public function getFileSize()
  {
    return $this->_fileInfo['size'];
  }

  /**
   * アップロードされたファイルを保存します。
   *
   * @param string $savePath ファイルを保存するパス。APP_ROOT_DIR からの相対パス、あるいは絶対パスが有効。
   *   パスに含まれるディレクトリが存在しない場合は自動的に生成されます。
   * @return bool ファイルの保存に成功した場合は TRUE、失敗した場合は FALSE を返します。
   */
  public function deploy($savePath)
  {
    if (!PHI_FileUtils::isAbsolutePath($savePath)) {
      $savePath = APP_ROOT_DIR . DIRECTORY_SEPARATOR . $savePath;
    }

    $baseDirectory = dirname($savePath);

    if (!is_dir($baseDirectory)) {
      PHI_FileUtils::createDirectory($baseDirectory);
    }

    if (move_uploaded_file($this->getTemporaryFilePath(), $savePath)) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * @param $name
   * @return array|null
   */
  public static function getFileInfo($name)
  {
    $fileInfo = NULL;

    if (($pos = strpos($name, '.')) !== FALSE) {
      $types = array('name', 'tmp_name', 'type', 'size', 'error');
      $array = array();

      foreach ($types as $type) {
        $search = sprintf('%s.%s.%s',
          substr($name, 0, $pos),
          $type,
          substr($name, $pos + 1));
        $data = PHI_ArrayUtils::find($_FILES, $search);

        if ($data !== NULL) {
          $array[$type] = $data;
        }
      }

      if (sizeof($array)) {
        $fileInfo = $array;
      }

    } else {
      if (isset($_FILES[$name])) {
        $fileInfo = $_FILES[$name];
      }
    }

    return $fileInfo;
  }
}
