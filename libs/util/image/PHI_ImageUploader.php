<?php
/**
 * イメージファイルのアップロード機能を提供します。
 *
 * @package util.image
 */
class PHI_ImageUploader extends PHI_FileUploader
{
  /**
   * @var string
   */
  private $_imageEngine = PHI_ImageFactory::IMAGE_ENGINE_GD;

  /**
   * イメージエンジンを設定します。
   * このメソッドは、{@link getImage()} メソッドでイメージオブジェクトを取得する前に呼び出す必要があります。
   * 特に指定がない場合は GD エンジンが使用されます。
   *
   * @param int $imageEngine PHI_Factory::IMAGE_ENGINE_* 定数を指定。
   * @throw PHI_UnsupportedException PHP がイメージエンジンをサポートしていない場合に発生。
   */
  public function setImageEngine($imageEngine)
  {
    if (!extension_loaded($imageEngine)) {
      $message = sprintf('Library it not found. [%s]', $imageEngine);
      throw new PHI_UnsupportedException($message);
    }

    $this->_imageEngine = $imageEngine;
  }

  /**
   * アップロードされたファイルのイメージオブジェクトを取得します。
   *
   * @param bool $adjustOrientation {@link PHI_ImageDelegate::createOriginalImageFromFile()} メソッドを参照。
   * @return PHI_Image PHI_Image のインスタンスを返します。
   */
  public function getImage($adjustOrientation = TRUE)
  {
    $image = PHI_ImageFactory::create($this->_imageEngine);
    $image->load($this->getTemporaryFilePath(), $adjustOrientation);

    return $image;
  }
}
