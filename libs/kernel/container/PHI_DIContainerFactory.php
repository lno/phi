<?php
/**
 * DI コンテナを生成するためのファクトリクラスです。
 *
 * @package kernel.container
 */
class PHI_DIContainerFactory
{
  /**
   * {@link PHI_DIContainer} オブジェクト。
   * @var PHI_DIContainer
   */
  private static $_container;

  /**
   * プライベートコンストラクタ。
   */
  private function __construct()
  {}

  /**
   * DI コンテナを初期化します。
   */
  public static function initialize()
  {
    self::$_container = new PHI_DIContainer();
  }

  /**
   * PHI_DIContainer オブジェクトを取得します。
   *
   * @return PHI_DIContainer DI コンテナのインスタンスを返します。
   */
  public static function getContainer()
  {
    if (self::$_container === NULL) {
      throw new RuntimeException('DI container is not initialized.');
    }

    return self::$_container;
  }
}
