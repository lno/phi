<?php
/**
 * アプリケーションレベルのエラーが発生した際に通知される例外です。
 * ビジネスロジック上で発生する例外は、派生クラス {@link PHI_BusinessLogicException} を使用して下さい。
 *
 * @package exception
 */
class PHI_ApplicationException extends PHI_Exception
{}
