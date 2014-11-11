<?php
/**
 * {@link PHI_Image} を利用したイメージライブラリクラスが実装すべきメソッドを定義する抽象クラスです。
 *
 * @package util.image
 */
abstract class PHI_ImageDelegate extends PHI_Object
{
  /**
   * {@link PHI_Image} オブジェクト。
   * @var PHI_Image
   */
  protected $_image;

  /**
   * イメージの方向。(Exif: Orientation タグ)
   * @var int
   */
  protected $_orientation;

  /**
   * JPEG 圧縮率。
   * @var int
   */
  protected $_jpegQuality = 75;

  /**
   * PNG 圧縮率。
   * @var int
   */
  protected $_pngQuality = 2;

  /**
   * PHI_Image オブジェクトをセットします。
   *
   * @param PHI_Image $image イメージのインスタンス。
   */
  public function setImage(PHI_Image $image)
  {
    $this->_image = $image;
  }

  /**
   * イメージエンジンのタイプを取得します。
   *
   * @return string PHI_ImageFactory::IMAGE_ENGINE_* 定数を返します。
   */
  abstract public function getImageEngine();

  /**
   * イメージファイルからイメージオブジェクト (またはリソース) を生成します。
   *
   * @param string $path 参照するイメージのパス。
   * @param int $type イメージの形式。PHI_Image::IMAGE_TYPE_* 定数を参照。
   * @return mixed ライブラリが提供するイメージオブジェクト、またはリソース型を返します。
   * @param bool $adjustOrientation TRUE を指定した場合、JPEG イメージの方向を Exif の Orientation タグに基づいて補正します。
   *   - {@link http://www.php.net/manual/book.exif.php Exif モジュール} がインストールされている場合のみ有効です。
   *     モジュールがインストールされていない場合は FALSE (生の画像を読み込む) と同じ動作になります。
   *   - GD エンジン利用時の補足: GD は回転補正は行うものの、イメージ反転 (水平反転・垂直反転) 補正を行いません。
   *   - ImageMagick エンジン利用時の補足: Imagick が ImageMagick 6.3 以降でコンパイルされている場合、Exif モジュールのインストールは不要となります。
   * @return mixed ライブラリが提供するイメージオブジェクト、またはリソース型を返します。
   *   オブジェクトの生成に失敗した場合は FALSE を返します。
   */
  abstract public function createOriginalImageFromFile($path, $type, $adjustOrientation = TRUE);

  /**
   * JPEG イメージの方向を補正します。
   * このメソッドは {@link createOriginalImageFromFile()} メソッドからコールされます。
   *
   * @param mixed &$image ライブラリが提供するイメージオブジェクト、またはリソース型。
   * @link http://sylvana.net/jpegcrop/exif_orientation.html Exif Orientation Tag
   */
  abstract public function adjustOrientation(&$image);

  /**
   * バイナリデータからイメージオブジェクトを生成します。
   *
   * @param string $data 元となるバイナリデータ。
   * @return mixed ライブラリが提供するイメージオブジェクト、またはリソース型を返します。
   *   オブジェクトの生成に失敗した場合は FALSE を返します。
   */
  abstract public function createOriginalImageFromBinary($data);

  /**
   * オリジナルイメージのサイズを取得します。
   *
   * @param mixed &$image ライブラリが提供するイメージオブジェクト、またはリソース型。
   * @return array イメージサイズが格納された添字配列を返します。(0:横幅、1:縦幅)
   *   サイズ取得に失敗した場合は FALSE を返します。
   */
  abstract public function getOriginalImageBounds(&$image);

  /**
   * イメージをリサイズします。
   *
   * @param mixed &$image ライブラリが提供するイメージオブジェクト、またはリソース型。
   * @param int $fromWidth 元イメージの横幅サイズ。
   * @param int $fromHeight 元イメージの縦幅サイズ。
   * @param int $toWidth リサイズ後の横幅サイズ。
   * @param int $toHeight リサイズ後の縦幅サイズ。
   * @return bool リサイズに成功した場合は TRUE、失敗した場合は FALSE を返します。
   */
  abstract public function resize(&$image, $fromWidth, $fromHeight, $toWidth, $toHeight);

  /**
   * @param mixed &$image ライブラリが提供するイメージオブジェクト、またはリソース型。
   * @param array $parameters トリミング情報を格納した配列。
   *   - newWidth: 新しいキャンバスの高さ。
   *   - newHeight: 新しいキャンバスの幅。
   *   - toXPos: キャンバスにトリミングイメージをコピーする際の開始座標。(X 軸)
   *   - toYPos: キャンバスにトリミングイメージをコピーする際の開始座標。(Y 軸)
   *   - fromXPos: トリミング開始座標。(X 軸)
   *   - fromYPos: トリミング開始座標。(Y 軸)
   *   - toWidth: キャンバスにコピーするトリミングイメージの幅。
   *   - toHeight:キャンバスにコピーするトリミングイメージの高さ。
   *   - fromWidth: トリミングするイメージの幅。
   *   - fromHeight: トリミングするイメージの高さ。
   *   - fillColor: 余白部分を塗りつぶす色。({@link PHI_ImageColor} オブジェクトのインスタンス)
   * @see PHI_Image::trim()
   */
  abstract public function trim(&$image, $parameters);

  /**
   * JPEG の品質を設定します。
   *
   * @param int $jpegQuality {@link PHI_Image::setJPEGQuality()} メソッドを参照。
   */
  public function setJPEGQuality($jpegQuality)
  {
    $this->_jpegQuality = $jpegQuality;
  }

  /**
   * PNG の品質を設定します。
   *
   * @param int $pngQuality {@link PHI_Image::setPNGQuality()} メソッドを参照。
   */
  public function setPNGQuality($pngQuality)
  {
    $this->_pngQuality = $pngQuality;
  }

  /**
   * イメージ形式を変換します。
   *
   * @param mixed &$image ライブラリが提供するイメージオブジェクト、またはリソース型。
   * @param int $fromType 元のイメージ形式。(PHI_Image::IMAGE_TYPE_* 定数)
   * @param int $toType 変換後のイメージ形式。(PHI_Image::IMAGE_TYPE_* 定数)
   * @param int $width 対象イメージの横幅サイズ。
   * @param int $height 対象イメージの縦幅サイズ。
   * @return bool 変換に成功した場合は TRUE、失敗した場合は FALSE を返します。
   */
  abstract public function convertFormat(&$image, $fromType, $toType, $width, $height);

  /**
   * イメージオブジェクトを出力、または保存します。
   *
   * @param mixed &$image ライブラリが提供するイメージオブジェクト、またはリソース型。
   * @param int $type 対象となるイメージ形式。(IMAGE_TYPE_* 定数)
   * @param string $path イメージの出力パス。NULL 指定時は画面に描画される。
   * @return bool 出力または保存に成功したかどうかを返します。
   */
  abstract public function createDestinationImage(&$image, $type, $path);

  /**
   * イメージデータを保持するオブジェクト (またはリソース) を取得します。
   *
   * @return mixed イメージデータを保持するオブジェクト (またはリソース) を返します。
   */
  public function getRawImage()
  {
    return $this->_image->getDestinationImage();
  }

  /**
   * 使用中のメモリ領域を開放します。
   *
   * @param mixed &$image ライブラリが提供するイメージオブジェクト、またはリソース型。
   */
  abstract public function clear(&$image);
}
