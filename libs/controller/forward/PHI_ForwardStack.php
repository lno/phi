<?php
/**
 * @package controller.forward
 */
class PHI_ForwardStack extends PHI_Object
{
  private $_forwardStack = array();

  public function add(PHI_Forward $forward)
  {
    if (sizeof($this->_forwardStack) > 8) {
      $message = 'Forward too many.';
      throw new OverflowException($message);
    }

    $this->_forwardStack[] = $forward;
  }

  public function getSize()
  {
    return sizeof($this->_forwardStack);
  }

  public function getPrevious()
  {
    $result = FALSE;
    $size = $this->getSize();

    if ($size > 2) {
      $result = $this->_forwardStack[$size - 2];
    }

    return $result;
  }

  /**
   * @return PHI_Forward|null
   */
  public function getLast()
  {
    $size = sizeof($this->_forwardStack);

    if ($size == 0) {
      throw new RuntimeException('Forward stack is empty.');
    }

    return $this->_forwardStack[$size - 1];
  }

  public function getStack()
  {
    return $this->_forwardStack;
  }
}

