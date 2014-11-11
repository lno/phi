<?php
/**
 * ヘルパのインスタンスを管理します。
 *
 * @package view.helper
 */
class PHI_HelperManager extends PHI_Object
{
  /**
   * @var PHI_View
   */
  private $_view;

  /**
   * @var PHI_ParameterHolder
   */
  private $_config;

  /**
   * @var array
   */
  private $_instances = array();

  /**
   * コンストラクタ。
   *
   * @param PHI_View $view ヘルパを適用するビューオブジェクト。
   */
  public function __construct(PHI_View $view)
  {
    $this->_view = $view;
    $this->_config = PHI_Config::getHelpers();
  }

  /**
   * ヘルパマネージャにヘルパを追加します。
   *
   * <code>
   * $manager->addHelper('custom', array('class' => 'CustomHelper'));
   * </code>
   *
   * @param string $helperId ヘルパ ID。
   * @param array $parameters ヘルパのパラメータ。
   *   指定可能なパラメータは {@link PHI_Helper} クラスを参照して下さい。
   */
  public function addHelper($helperId, array $parameters = array())
  {
    $this->_config->set($helperId, $parameters);
  }

  /**
   * ヘルパマネージャにヘルパが登録されているかチェックします。
   *
   * @param string $helperId チェック対象のヘルパ ID。
   * @return bool ヘルパが登録されている場合は TRUE、登録されていない場合は FALSE を返します。
   */
  public function hasHelper($helperId)
  {
    return $this->_config->hasName($helperId);
  }

  /**
   * ヘルパマネージャに登録されているヘルパを削除します。
   *
   * @param string $helperId 削除対象のヘルパ ID。
   * @return bool 削除が成功した場合は TRUE、失敗した場合は FALSE を返します。
   */
  public function removeHelper($helperId)
  {
    return $this->_config->remove($helperId);
  }

  /**
   * ヘルパのインスタンスを取得します。
   *
   * @param string $helperId 取得対象のヘルパ ID。
   * @return PHI_Helper ヘルパのインスタンスを返します。
   * @throws PHI_ConfigurationException 指定されたヘルパが味登録の場合に発生。
   */
  public function getHelper($helperId)
  {
    $config = $this->_config->get($helperId);

    if ($config) {
      $className = $config->getString('class');

      if (empty($this->_instances[$className])) {
        PHI_ClassLoader::loadByPath($config->getString('path'), $className);

        $this->_instances[$className] = new $className($this->_view, $config->toArray());
        $this->_instances[$className]->initialize();
      }

      return $this->_instances[$className];

    } else {
      $message = sprintf('Helper is not registered in PHI_HelperManager. [%s]', $helperId);
      throw new PHI_ConfigurationException($message);
    }
  }

  /**
   * ヘルパマネージャに登録されている全てのヘルパ情報を取得します。
   *
   * @return PHI_ParameterHolder ヘルパマネージャに登録されている全てのヘルパ情報を取得します。
   */
  public function getConfig()
  {
    return $this->_config;
  }

  /**
   * 登録されている全てのヘルパを破棄します。
   */
  public function clear()
  {
    $this->_config = array();
    $this->_instances = array();
  }
}
