<?php
/**
 * @package console.{%PACKAGE_NAME%}
 */
class HelloWorldCommand extends PHI_ConsoleCommand
{
  public function execute()
  {
    $this->getOutput()->writeLine('Hello World!');
  }
}
