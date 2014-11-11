<?php
/**
 * イメージを扱うためのユーティリティライブラリを提供します。
 *
 * @package util.image
 */
class PHI_ImageFactory extends PHI_Object
{
  /**
   * GD ライブラリ定数。
   */
  const IMAGE_ENGINE_GD = 'gd';

  /**
   * ImageMagick ライブラリ定数。
   */
  const IMAGE_ENGINE_IMAGE_MAGICK = 'imagick';

  /**
   * 指定したイメージエンジンがシステム内で有効な状態にあるかどうかチェックします。
   *
   * @param string $type IMAGE_ENGINE_* 定数を指定。
   * @return bool イメージエンジンが有効な場合は TRUE、無効な場合は FALSE を返します。
   */
  public static function isEnableImageEngine($type)
  {
    return extension_loaded($type);
  }

  /**
   * PHI_ImageFactory がサポートしているイメージエンジンのリストを取得します。
   *
   * @return array サポートしているイメージエンジンの定数 (IMAGE_ENGINE_*) を配列形式で返します。
   */
  public static function getSupportedImageEngine()
  {
    $array = array();

    if (self::isEnableImageEngine(self::IMAGE_ENGINE_GD)) {
      $array[] = self::IMAGE_ENGINE_GD;
    }

    if (self::isEnableImageEngine(self::IMAGE_ENGINE_IMAGE_MAGICK)) {
      $array[] = self::IMAGE_ENGINE_IMAGE_MAGICK;
    }

    return $array;
  }

  /**
   * イメージを扱うためのユーティリティクラスを取得します。
   *
   * @param string $type 使用するイメージエンジン。IMAGE_ENGINE_* 定数を指定。
   * @return PHI_Image PHI_Image オブジェクトのインスタンスを返します。
   * @throws PHI_UnsupportedException ライブラリが使用可能な状態でない場合に発生。
   */
  public static function create($type = self::IMAGE_ENGINE_GD)
  {
    if (!self::isEnableImageEngine($type)) {
      $message = sprintf('Extension is not loaded. [%s]', $type);
      throw new PHI_UnsupportedException($message);
    }

    $delegate = NULL;

    switch ($type) {
      case self::IMAGE_ENGINE_GD:
        $delegate = new PHI_ImageGDDelegate();
        break;

      case self::IMAGE_ENGINE_IMAGE_MAGICK:
        $delegate = new PHI_ImageImageMagickDelegate();
        break;

      default:
        $message = sprintf('Library is not available. [%s]', $type);
        throw new PHI_UnsupportedException($message);
    }

    return self::createFromDelegate($delegate);
  }

  /**
   * イメージを扱うためのユーティリティクラスを取得します。
   *
   * @param PHI_ImageDelegate $delegate PHI_ImageDelegate を実装したイメージクラスのインスタンス。
   * @return PHI_Image PHI_Image オブジェクトのインスタンスを返します。
   */
  public static function createFromDelegate(PHI_ImageDelegate $delegate)
  {
    $instance = new PHI_Image();
    $instance->setDelegate($delegate);

    return $instance;
  }
}
