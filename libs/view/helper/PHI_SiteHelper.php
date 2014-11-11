<?php
/**
 * サイト設定ファイルから値を参照するためのヘルパメソッドを提供します。
 * このヘルパは、$site という変数名であらかじめテンプレートにインスタンスが割り当てられています。
 *
 * <code>
 * <?php echo $site->{method}; ?>
 * </code>
 *
 * global_helpers.yml の設定例:
 * <code>
 * site:
 *   # ヘルパクラス名。
 *   class: PHI_SiteHelper
 * </code>
 * <i>その他に指定可能な属性は {@link PHI_Helper} クラスを参照。</i>
 *
 * @package view.helper
 */
class PHI_SiteHelper extends PHI_Helper
{
  /**
   * @var array
   */
  private $_siteConfig;

  /**
   * @see PHI_Helper::__construct()
   */
  public function __construct(PHI_View $currentView, array $config = array())
  {
    parent::__construct($currentView, $config);

    $this->_siteConfig = PHI_Config::getSite();
  }

  /**
   * site.yml に定義された name の値を取得します。
   * このメソッドは {@link PHI_siteConfig::getSite()} から取得した値を {@link PHI_StringUtils::escape() HTML エスケープ} した形式で返します。
   *
   * @param string $name 検索対象の属性名。
   * @param mixed $alternative name 属性が見つからない場合に返す代替値。
   * @return mixed 指定した属性に対応する値を返します。
   */
  public function get($name, $alternative = NULL)
  {
    $value = $this->_siteConfig->get($name, $alternative);

    if ($value !== NULL) {
      $value = PHI_StringUtils::escape($value);
    }

    return $value;
  }
}
