<?php
/**
 * {@link PHI_ExceptionHandler} で捕捉した例外を扱うための抽象クラスです。
 *
 * @package exception.delegate
 */
abstract class PHI_ExceptionDelegate extends PHI_Object
{
  /**
   * アプリケーションの実行環境 (Web、またはコンソール) に合わせて例外を扱うためのメソッドを起動します。
   *   o Web アプリケーションの場合: {@link catchOnApplication()}、{@link catchOnWeb()} メソッドを実行します。
   *   o コンソールアプリケーションの場合: {@link catchOnApplication()}、{@link catchOnConsole()} メソッドを実行します。
   *
   * @param Exception $exception {@link Exception}、または Exception を継承した例外オブジェクトのインスタンス。
   * @param PHI_ParameterHolder $holder 例外デリゲートオプション。
   */
  public static function invoker(Exception $exception, PHI_ParameterHolder $holder = NULL)
  {
    if ($holder === NULL) {
      $holder = new PHI_ParameterHolder();
    }

    static::catchOnApplication($exception, $holder);

    if (PHI_BootLoader::isBootTypeWeb()) {
      static::catchOnWeb($exception, $holder);

    } else {
      static::catchOnConsole($exception, $holder);
    }
  }

  /**
   * アプリケーションから例外がスローされた際に、{@link invoker()} メソッドによってコールされます。
   *
   * @see PHI_ExceptionDelegate::invoker()
   */
  protected static function catchOnApplication(Exception $exception, PHI_ParameterHolder $holder)
  {}

  /**
   * Web アプリケーションで例外がスローされた際に、{@link invoker()} メソッドによってコールされます。
   * このメソッドは、{@link catchOnApplication()} がコールされた後に実行されます。
   *
   * @param Exception $exception {@link invoker()} メソッドを参照。
   * @see PHI_ExceptionDelegate::invoker()
   */
  protected static function catchOnWeb(Exception $exception, PHI_ParameterHolder $holder)
  {}

  /**
   * コンソールアプリケーションで例外がスローされた際に、{@link invoker()} メソッドによってコールされます。
   * このメソッドは、{@link catchOnApplication()} がコールされた後に実行されます。
   *
   * @param Exception $exception {@link invoker()} メソッドを参照。
   * @see PHI_ExceptionDelegate::invoker()
   */
  protected static function catchOnConsole(Exception $exception, PHI_ParameterHolder $holder)
  {}
}
