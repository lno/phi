<?php
/**
 * カスタム設定ファイルのパラメータを管理します。
 *
 * @package util.config
 */
class PHI_ConfigCustomHolder extends PHI_ParameterHolder
{
  /**
   * @var string
   */
  private $_path;

  /**
   * コンストラクタ。
   *
   * @param string $path ファイルパス。
   * @param array $config パラメータデータ。
   */
  public function __construct($path, array $config = array())
  {
    $this->_path = $path;

    parent::__construct($config, TRUE);
  }

  /**
   * 設定ファイルを更新します。
   * このメソッドは配列データを元に YAML フォーマットを生成します。
   * ファイルに含まれるコメントは全て除去される点に注意して下さい。
   */
  public function update()
  {
    $directory = dirname($this->_path);

    if (!is_dir($directory)) {
      PHI_FileUtils::createDirectory($directory);
    }

    PHI_CommonUtils::loadVendorLibrary('spyc/spyc.php');

    $array = $this->toArray();

     // 配列を YAML 形式に変換
    $data = Spyc::YAMLDump($array, 2, 76);

    // サニタイズ処理
    $data = preg_replace('/^([a-zA-Z0-9]+)/m', "\n" . '\1', $data);
    $data = trim($data, "---\n") . "\n";

    PHI_FileUtils::writeFile($this->_path, $data, LOCK_EX);
  }

  /**
   * 設定ファイルを削除します。
   *
   * @return bool ファイルの削除に成功した場合は TRUE、失敗した場合は FALSE を返します。
   */
  public function delete()
  {
    $result = FALSE;

    if (is_file($this->_path)) {
      $result = PHI_FileUtils::deleteFile($this->_path);
    }

    return $result;
  }
}
