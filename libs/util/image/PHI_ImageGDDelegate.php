<?php
/**
 * GD を使用してイメージの作成や修正を行います。
 * 本クラスを利用するには、GD ライブラリのインストールと、PHP の GD サポートを有効化する必要があります。
 *
 * @link http://www.boutell.com/gd/ GD Graphics Library
 * @link http://www.php.net/manual/image.installation.php GD Installation
 * @package util.image
 */
class PHI_ImageGDDelegate extends PHI_ImageDelegate
{
  /**
   * 透過処理が行われているか。
   * @var bool
   */
  private $_isSavedTransparent = FALSE;

  /**
   * @see PHI_ImageDelegate::getImageEngine()
   */
  public function getImageEngine()
  {
    return PHI_ImageFactory::IMAGE_ENGINE_GD;
  }

  /**
   * @see PHI_ImageDelegate::createOriginalImageFromFile()
   */
  public function createOriginalImageFromFile($path, $type, $adjustOrientation = TRUE)
  {
    $image = FALSE;

    // @エラー制御演算子を付けて失敗時は FALSE を返すようにする
    switch ($type) {
      case PHI_Image::IMAGE_TYPE_GIF:
        $image = @imagecreatefromgif($path);
        break;

      case PHI_Image::IMAGE_TYPE_JPEG:
        $image = @imagecreatefromjpeg($path);

        if (extension_loaded('exif')) {
          // EXIF データがない場合、"Incorrect APP1 Exif Identifier Code" エラーが起こる場合がある
          $exif = @read_exif_data($path);

          if (isset($exif['Orientation'])) {
            $this->_orientation = $exif['Orientation'];
          }
        }

        if ($adjustOrientation) {
          $this->adjustOrientation($image);
        }

        break;

      case PHI_Image::IMAGE_TYPE_PNG:
        $image = @imagecreatefrompng($path);
        break;
    }

    return $image;
  }

  /**
   * @see PHI_ImageDelegate::adjustOrientation()
   */
  public function adjustOrientation(&$image)
  {
    switch ($this->_orientation) {
      case 1:
      case 2:
      case 4:
      case 5:
      case 7:
        break;

      case 3:
        $image = imagerotate($image, 180, 0);
        break;

      case 6:
        $image = imagerotate($image, 270, 0);
        break;

      case 8:
        $image = imagerotate($image, 90, 0);
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
    return imagecreatefromstring($data);
  }

  /**
   * @see PHI_ImageDelegate::getOriginalImageBounds()
   */
  public function getOriginalImageBounds(&$image)
  {
    $array = array();
    $array[] = imagesx($image);
    $array[] = imagesy($image);

    return $array;
  }

  /**
   * @see PHI_ImageDelegate::resize()
   */
  public function resize(&$image, $width, $height, $resizeWidth, $resizeHeight)
  {
    $type = $this->_image->getDestinationImageAttribute('type');
    $resizeImage = $this->saveTransparent($image, $resizeWidth, $resizeHeight);

    $result = imagecopyresampled($resizeImage,
      $image,
      0,
      0,
      0,
      0,
      $resizeWidth,
      $resizeHeight,
      $width,
      $height);

    $image = $resizeImage;

    return $result;
  }

  /**
   * @see PHI_ImageDelegate::trim()
   */
  public function trim(&$image, $parameters)
  {
    $fillColor = imagecolorallocate($image,
      $parameters['fillColor']->getRed(),
      $parameters['fillColor']->getGreen(),
      $parameters['fillColor']->getBlue());

    $newImage = $this->saveTransparent($image,
      $parameters['newWidth'],
      $parameters['newHeight'],
      $fillColor);

    imagefill($image, 0, 0, $fillColor);
    $result = imagecopyresampled($newImage,
      $image,
      $parameters['toXPos'],
      $parameters['toYPos'],
      $parameters['fromXPos'],
      $parameters['fromYPos'],
      $parameters['toWidth'],
      $parameters['toHeight'],
      $parameters['fromWidth'],
      $parameters['fromHeight']);

    $image = $newImage;
  }

  /**
   * @see PHI_ImageDelegate::convertToFormat()
   */
  public function convertFormat(&$image, $fromType, $toType, $width, $height)
  {
    if ($fromType == $toType) {
      return TRUE;
    }

    if ($fromType == PHI_Image::IMAGE_TYPE_GIF || $fromType == PHI_Image::IMAGE_TYPE_PNG) {
      $convertImage = $this->saveTransparent($image, $width, $height);

    } else if ($toType == PHI_Image::IMAGE_TYPE_GIF) {
      // 元イメージを 256 色パレットに変更
      imagetruecolortopalette($image, TRUE, 256);
      $convertImage = imagecreatetruecolor($width, $height);
    }

    $result = imagecopy($convertImage,
      $image,
      0,
      0,
      0,
      0,
      $width,
      $height);

    $image = $convertImage;

    return $result;
  }

  /**
   * @see PHI_ImageDelegate::createDestinationImage()
   */
  public function createDestinationImage(&$image, $type, $path)
  {
    $result = FALSE;

    switch ($type) {
      case PHI_Image::IMAGE_TYPE_GIF:
        $result = imagegif($image, $path);
        break;

      case PHI_Image::IMAGE_TYPE_JPEG:
        $result = imagejpeg($image, $path, $this->_jpegQuality);
        break;

      case PHI_Image::IMAGE_TYPE_PNG:
        if (!$this->_isSavedTransparent) {
          $this->saveTransparent($image);
        }

        $result = imagepng($image, $path, $this->_pngQuality);
        break;
    }

    return $result;
  }

  /**
   * GIF、PNG の透過色を保持します。
   *
   * @param resource $image
   * @param int $width
   * @param int $height
   * @param int $fillColor
   */
  private function saveTransparent($image, $width = NULL, $height = NULL, $fillColor = NULL)
  {
    // width、height が指定されている場合は新しいイメージを生成
    if ($width !== NULL && $height !== NULL) {
      $newImage = imagecreatetruecolor($width, $height);

    } else {
      $newImage = $image;
    }

    $type = $this->_image->getDestinationImageAttribute('type');

    if ($type == PHI_Image::IMAGE_TYPE_GIF || $type == PHI_Image::IMAGE_TYPE_PNG) {
      // オリジナルイメージに定義された透明色を取得
      $index = imagecolortransparent($image);

      if ($index >= 0) {
        if ($fillColor === NULL) {
          $color = imagecolorsforindex($newImage, $index);
          $index = imagecolorallocate($newImage, $color['red'], $color['green'], $color['blue']);

          imagefill($newImage, 0, 0, $index);
          imagecolortransparent($newImage, $index);

        } else {
          imagefill($newImage, 0, 0, $fillColor);
        }

      // パレットに透過色が含まれていない場合 (アルファチャンネルが使用されている)
      } else if ($type == PHI_Image::IMAGE_TYPE_PNG) {
        imagealphablending($newImage, TRUE);
        imagesavealpha($newImage, TRUE);

        if ($fillColor === NULL) {
          $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);

          // 16777215 は塗りつぶしなし
          if ($transparent != 16777215) {
            imagefill($newImage, 0, 0, $transparent);
            imagecolordeallocate($newImage, $transparent);
          }

        } else {
          imagefill($newImage, 0, 0, $fillColor);
        }
      }
    }

    $this->_isSavedTransparent = TRUE;

    return $newImage;
  }

  /**
   * @see PHI_ImageDelegate::clear()
   */
  public function clear(&$image)
  {
    if (is_resource($image)) {
      imagedestroy($image);
      $image = NULL;
    }
  }
}
