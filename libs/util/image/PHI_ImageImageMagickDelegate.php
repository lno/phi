<?php
/**
 * ImageMagick を使用してイメージの作成や修正を行います。
 * 本クラスを利用するには、ImageMagick 及び Imagick のインストールが必要です。
 *
 * @link http://www.imagemagick.org/script/index.php ImageMagick
 * @link http://www.php.net/manual/imagick.installation.php Imagick Installation
 * @package util.image
 */
class PHI_ImageImageMagickDelegate extends PHI_ImageDelegate
{
  /**
   * @see PHI_ImageDelegate::getImageEngine()
   */
  public function getImageEngine()
  {
    return PHI_ImageFactory::IMAGE_ENGINE_IMAGE_MAGICK;
  }

  /**
   * @see PHI_ImageDelegate::createOriginalImageFromFile()
   */
  public function createOriginalImageFromFile($path, $type, $adjustOrientation = TRUE)
  {
    $image = new Imagick($path);

    if ($type == PHI_Image::IMAGE_TYPE_JPEG) {
      if (method_exists($image, 'getImageOrientation')) {
        $this->_orientation = $image->getImageOrientation();

      } else if (extension_loaded('exif')) {
        $exif = read_exif_data($path);

        if (isset($exif['Orientation'])) {
          $this->_orientation = $exif['Orientation'];
        }
      }

      if ($adjustOrientation) {
        $this->adjustOrientation($image);
      }
    }

    return $image->getImage();
  }

  /**
   * @see PHI_ImageDelegate::adjustOrientation()
   */
  public function adjustOrientation(&$image)
  {
    switch ($this->_orientation) {
      case 1:
        break;

      case 2:
        $image->flopImage();
        break;

      case 3:
        $image->rotateImage(new ImagickPixel(), 180);
        break;

      case 4:
        $image->flipImage();
        break;

      case 5:
        $image->rotateImage(new ImagickPixel(), 270);
        $image->flipImage();
        break;

      case 6:
        $image->rotateImage(new ImagickPixel(), 90);
        break;

      case 7:
        $image->rotateImage(new ImagickPixel(), 90);
        $image->flipImage();
        break;

      case 8:
        $image->rotateImage(new ImagickPixel(), 270);
        break;

      default:
        break;
    }
  }

  /**
   * @see PHI_ImageDelegate::createOriginalImageFromBinary()
   */
  public function createOriginalImageFromBinary($data)
  {
    try {
      $image = new Imagick();
      $image->readImageBlob($data);

      if (method_exists($image, 'getImageOrientation')) {
        $this->_orientation = $image->getImageOrientation();
      }

      return $image->getImage();

    } catch (ImagickException $e) {
      return FALSE;
    }
  }

  /**
   * @see PHI_ImageDelegate::getOriginalImageBounds()
   */
  public function getOriginalImageBounds(&$image)
  {
    try {
      $attributes = $image->getImageGeometry();

      $array = array();
      $array[] = $attributes['width'];
      $array[] = $attributes['height'];

      return $array;

    } catch (ImagickException $e) {
      return FALSE;
    }
  }

  /**
   * @see PHI_ImageDelegate::resize()
   */
  public function resize(&$image, $width, $height, $resizeWidth, $resizeHeight)
  {
    return $image->thumbnailImage($resizeWidth, $resizeHeight);
  }

  /**
   * @see PHI_ImageDelegate::trim()
   */
  public function trim(&$image, $parameters)
  {
    try {
      $image->cropImage($parameters['newWidth'],
        $parameters['newHeight'],
        $parameters['fromXPos'],
        $parameters['fromYPos']);

      // setImagePage() を実行しないと GIF イメージをトリミングした際に画像サイズが元のままとなってしまう
      $image->setImagePage($parameters['newWidth'], $parameters['newHeight'], 0, 0);

      if ($parameters['toWidth'] < $parameters['newWidth'] ||
          $parameters['toHeight'] < $parameters['newHeight']) {

        $fillColor = new ImagickPixel();
        $fillColor->setColor($parameters['fillColor']->getHTMLColor());

        $type = $this->_image->getDestinationImageAttribute('type');
        $imageFormat = $this->getImageFormat($type);

        $newImage = new Imagick();
        $newImage->newimage($parameters['newWidth'], $parameters['newHeight'], $fillColor);
        $newImage->setImageFormat($imageFormat);

        $newImage->compositeImage($image,
          $image->getImageCompose(),
          $parameters['toXPos'],
          $parameters['toYPos']);
        $fillColor->clear();
        $fillColor->destroy();

        $image = $newImage;
      }

      return TRUE;

    } catch (ImagickException $e) {
      return FALSE;
    }
  }

  /**
   * @see PHI_ImageDelegate::convertToFormat()
   */
  public function convertFormat(&$image, $fromType, $toType, $width, $height)
  {
    if ($fromType == $toType) {
      return TRUE;
    }

    if ($image->setImageFormat($this->getImageFormat($toType))) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * @see PHI_ImageDelegate::createDestinationImage()
   */
  public function createDestinationImage(&$image, $type, $path)
  {
    $result = FALSE;

    try {
      $image->stripImage();

      if ($type == PHI_Image::IMAGE_TYPE_JPEG) {
        $image->setCompression(Imagick::COMPRESSION_JPEG);
        $image->setImageCompressionQuality($this->_jpegQuality);

      } else if ($type == PHI_Image::IMAGE_TYPE_PNG) {
        $image->setCompression(Imagick::COMPRESSION_ZIP);
        $image->setImageCompressionQuality($this->_pngQuality);
      }

      if ($path === NULL) {
        echo $image->getImageBlob();

        $result = TRUE;
      } else {
        $result = $image->writeImage($path);
      }

    } catch (ImagickException $e) {
      $result = FALSE;
    }

    return $result;
  }

  /**
   * @see PHI_ImageDelegate::clear()
   */
  public function clear(&$image)
  {
    $image->clear();
    $image->destroy();
  }

  /**
   * PHI_Image::IMAGE_TYPE_* 定数を {@link http://php.net/manual/function.imagick-setimageformat.php Imagick::setImageFormat()} が解釈できるフォーマット文字列に変換します。
   *
   * @param int $type PHI_Image::IMAGE_TYPE_* 定数。
   * @return string {@link http://php.net/manual/function.imagick-setimageformat.php Imagick::setImageFormat()} が解釈できるフォーマット文字列を返します。
   */
  private function getImageFormat($type)
  {
     $mimeType = image_type_to_mime_type($type);
     $imageFormat = substr($mimeType, strpos($mimeType, '/') + 1);

     return $imageFormat;
  }
}
