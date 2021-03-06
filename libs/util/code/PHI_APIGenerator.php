<?php
/**
 * このクラスは、実験的なステータスにあります。
 * これは、この関数の動作、関数名、ここで書かれていること全てが phi の将来のバージョンで予告なく変更される可能性があることを意味します。
 * 注意を喚起するとともに自分のリスクでこのクラスを使用してください。
 *
 * @package util.code
 */
class PHI_APIGenerator extends PHI_Object
{
  /**
   * @var string
   */
  protected $_parseDirectory;

  /**
   * @var string
   */
  protected $_templateDirectory;

  /**
   * @var PHI_View
   */
  protected $_view;

  /**
   * @var array
   */
  protected $_excludeDirectories = array();

  /**
   * @var string
   */
  protected $_title = 'Class reference';

  /**
   * @var array
   */
  protected $_indexes = array();

  /**
   * @var array
   */
  protected $_summaries = array();

  /**
   * @var array
   */
  protected $_pages = array();

  /**
   * @var string
   */
  protected $_indexData = NULL;

  /**
   * @var array
   */
  protected $_referenceDataList = array();

  /**
   * @var PHI_DocumentMakeHelper
   */
  protected $_documentHelper;

  /**
   * @var string
   */
  protected $_indexPageRelativeIndexPath = '';

  /**
   * @var string
   */
  protected $_indexPageRelativeAPIPath = 'reference/';

  /**
   * @var string
   */
  protected $_referencePageRelativeIndexPath = '../../';

  /**
   * @var string
   */
  protected $_referencePageRelativeAPIPath = '../';

  /**
   * @var string
   */
  protected $_fileExtension = '.html';

  /**
   * @var string
   */
  protected $_linkExtension = '.html';

  /**
   * @param string $parseDirectory
   */
  public function __construct($parseDirectory)
  {
    $parseDirectory = realpath(str_replace('/', DIRECTORY_SEPARATOR, $parseDirectory));

    $this->_parseDirectory = $parseDirectory;
    $this->_templateDirectory = PHI_ROOT_DIR . DIRECTORY_SEPARATOR . 'skeleton' . DIRECTORY_SEPARATOR . 'api';
    $this->_outputDirectory   = APP_ROOT_DIR . DIRECTORY_SEPARATOR . 'data'     . DIRECTORY_SEPARATOR . 'api';

    $this->_view = new PHI_View(new PHI_BaseRenderer());
    $this->_view->importHelper('html');
  }

  /**
   * @param array $excludeDirectories
   */
  public function setExcludeDirectories(array $excludeDirectories)
  {
    $array = array();

    // 相対パスで指定された除外ディレクトリを絶対パス形式に変換
    foreach ($excludeDirectories as $excludeDirectory) {
      if (PHI_FileUtils::isAbsolutePath($excludeDirectory)) {
        $array[] = $excludeDirectory;
      } else {
        $array[] = $this->_parseDirectory . DIRECTORY_SEPARATOR . $excludeDirectory;
      }
    }

    $this->_excludeDirectories = $array;
  }

  /**
   * @param string $title
   */
  public function setTitle($title)
  {
    $this->_title = $title;
  }

  /**
   * @param string $templateDirectory
   */
  public function setTemplateDirectory($templateDirectory)
  {
    $this->_templateDirectory = $templateDirectory;
  }

  /**
   * @param string $outputDirectory
   */
  public function setOutputDirectory($outputDirectory)
  {
    $this->_outputDirectory = $outputDirectory;
  }

  /**
   * @return string
   */
  public function getOutputDirectory()
  {
    return $this->_outputDirectory;
  }

  /**
   * @param bool $clean
   * @param bool $outputError
   */
  public function make($clean = TRUE, $outputError = FALSE)
  {
    // 出力ディレクトリを作り直す
    if ($clean) {
      PHI_FileUtils::deleteDirectory($this->_outputDirectory);
    }

    // スクリプトディレクトリの解析
    $this->parse($outputError);

    // クラスの親子関係を解析
    $this->parseDependencyClasses();
    $this->parseDependencyClassMembers();
  }

  /**
   * @param string $absolutePath
   * @return string
   */
  protected function buildRelativePath($absolutePath)
  {
    $pos = strlen($this->_parseDirectory);
    $relativePath = str_replace('\\', '/', substr($absolutePath, $pos));

    return $relativePath;
  }

  /**
   * @param string $path
   * @param string $baseAnchor
   */
  protected function addFileIndex($path, $baseAnchor)
  {
    $fileName = basename($path);

    if (($pos = strpos($fileName, '.')) !== FALSE) {
      $fileName = substr($fileName, 0, $pos);
    }

    $this->_indexes['file'][$fileName] = $baseAnchor;
  }

  /**
   * @param string $className
   * @param string $baseAnchor
   */
  protected function addClassIndex($className, $baseAnchor)
  {
    $this->_indexes['class'][$className] = $baseAnchor;
  }

  /**
   * @param string $className
   * @param string $constantName
   * @param string $baseAnchor
   */
  protected function addConstantIndex($className, $constants, $baseAnchor)
  {
    foreach ($constants as $constantName => $attributes) {
      $key = sprintf('%s::%s', $className, $constantName);
      $value = sprintf('%s#constant_%s', $baseAnchor, $constantName);

      $this->_indexes['constant'][$key] = $value;
    }
  }

  /**
   * @param string $className
   * @param array $methods
   * @param string $baseAnchor
   */
  protected function addMethodIndex($className, array $methods, $baseAnchor)
  {
    foreach ($methods as $methodName => $attributes) {
      $key = sprintf('%s::%s()', $className, $methodName);
      $value = sprintf('%s#method_%s', $baseAnchor, $methodName);

      $this->_indexes['method'][$key] = $value;
    }
  }

  /**
   * @param string $className
   * @param array $properties
   * @param string $baseAnchor
   */
  protected function addPropertyIndex($className, array $properties, $baseAnchor)
  {
    foreach ($properties as $propertyName => $attributes) {
      $key = sprintf('%s::$%s', $className, $propertyName);
      $value = sprintf('%s#property_%s', $baseAnchor, $propertyName);

      $this->_indexes['property'][$key] = $value;
    }
  }

  /**
   * @param string $defineName
   * @param array $defines
   * @param string $baseAnchor
   */
  protected function addDefineIndex(array $defines, $baseAnchor)
  {
    foreach ($defines as $defineName => $attributes) {
      $key = $defineName;
      $value = sprintf('%s#define_%s', $baseAnchor, $defineName);

      $this->_indexes['define'][$key] = $value;
    }
  }

  /**
   * @param string $fileName
   * @param array $functions
   * @param string $baseAnchor
   */
  protected function addFunctionIndex($fileName, array $functions, $baseAnchor)
  {
    foreach ($functions as $functionName => $attributes) {
      $key = $functionName . '()';
      $value = sprintf('%s#function_%s', $baseAnchor, $functionName);

      $this->_indexes[$key] = $value;
    }
  }

  /**
   * @return string
   */
  protected function createAnchor($package, $fileName)
  {
    return $package
         . '/'
         . $fileName
         . $this->_linkExtension;
  }

  /**
   * @param string $package
   * @param string $fileName
   * @return string
   */
  protected function createReferencePath($package, $fileName)
  {
    return $this->_outputDirectory
         . DIRECTORY_SEPARATOR
         . 'reference'
         . DIRECTORY_SEPARATOR
         . $package
         . DIRECTORY_SEPARATOR
         . $fileName
         . $this->_fileExtension;
  }

  protected function getLayoutTemplatePath()
  {
    return $this->_templateDirectory . DIRECTORY_SEPARATOR . 'layout.php';
  }

  /**
   * @param bool $outputError
   */
  public function parse($outputError)
  {
    // ディレクトリ内のスクリプトを解析
    $pattern = '/^.*.php$/';
    $options = array('excludes' => $this->_excludeDirectories);
    $files = PHI_FileUtils::search($this->_parseDirectory, $pattern, $options);

    foreach ($files as $path) {
      $tokenizer = new PHI_Tokenizer($path, $outputError);
      $tokenizer->parse($outputError);

      $result = $tokenizer->getResult();

      $baseAnchor = $this->createAnchor($result['file']['package'], $result['file']['name']);
      $this->addFileIndex($path, $baseAnchor);

      // 関数の定義が含まれる場合
      if (isset($result['functions'])) {
        $relativePath = $this->buildRelativePath($result['file']['absolutePath']);
        $result['file']['relativePath'] = $relativePath;

        $fileName = $result['file']['name'];

        $array = array();
        $array['anchor'] = $this->createAnchor($result['file']['package'], $fileName);

        if (isset($result['file']['document']['summary'])) {
          $array['summary'] = $result['file']['document']['summary'];
        }

        $this->_summaries[$result['file']['package']][$fileName] = $array;
        $this->_pages[$path] = $result;

        if (isset($result['defines'])) {
          $this->addDefineIndex($result['defines'], $array['anchor']);
        }

        if (isset($result['functions'])) {
          $this->addFunctionIndex($fileName, $result['functions'], $array['anchor']);
        }
      }

      // クラスの定義が含まれる場合
      if (isset($result['classes'])) {
        foreach ($result['classes'] as $classId => $attributes) {
          $relativePath = $this->buildRelativePath($result['file']['absolutePath']);
          $attributes['relativePath'] = $relativePath;
          $className = $attributes['name'];

          $array = array();
          $array['anchor'] = $this->createAnchor($attributes['package'], $attributes['name']);

          if (isset($attributes['document']['summary'])) {
            $array['summary'] = $attributes['document']['summary'];
          }

          $this->_summaries[$attributes['package']][$className] = $array;
          $this->_pages[$path]['classes'][$classId] = $attributes;

          if (isset($attributes['methods'])) {
            $this->addMethodIndex($className, $attributes['methods'], $array['anchor']);
          }

          if (isset($attributes['properties'])) {
            $this->addPropertyIndex($className, $attributes['properties'], $array['anchor']);
          }

          $this->addClassIndex($className, $array['anchor']);

          if (isset($attributes['constants'])) {
            $this->addConstantIndex($className, $attributes['constants'], $array['anchor']);
          }
        }
      }
    }

    ksort($this->_summaries);
  }

  /**
   */
  public function build()
  {
    // ヘルパインスタンスの生成
    $this->_documentHelper = new PHI_DocumentMakeHelper($this->_view);
    $this->_documentHelper->setIndexes($this->_indexes);

    // インデックスの作成
    $this->_indexData = $this->createIndexPage();

    // リファレンスの作成
    $this->_referenceDataList = $this->createReferencePage();
  }

  /**
   */
  protected function parseDependencyClasses()
  {
    foreach ($this->_pages as $filePath => &$fileAttributes) {
      if (!isset($fileAttributes['classes'])) {
        continue;
      }

      $classes = &$fileAttributes['classes'];

      foreach ($classes as $classId => &$classAttributes) {
        // 親クラスの解析
        if (isset($classAttributes['inheritance'])) {
          $parentClassesInfo = $this->getParentClassesInfo($classAttributes['inheritance']);

          if (sizeof($parentClassesInfo)) {
            $inheritances = array();
            $interfaces = array();

            // 対象クラスがが依存している全てのインタフェース、親クラスを取得
            if (isset($classAttributes['interfaces'])) {
              $interfaces = $classAttributes['interfaces'];
            }

            foreach ($parentClassesInfo as $classInfo) {
              $inheritances[] = $classInfo['name'];

              if (isset($classInfo['interfaces'])) {
                foreach ($classInfo['interfaces'] as $interface) {
                  $interfaces[] = $interface;
                }
              }
            }

            $classAttributes['inheritanceTree'] = $inheritances;

            if (sizeof($interfaces)) {
              $interfaces = array_unique($interfaces);
              sort($interfaces);

              $classAttributes['interfaces'] = $interfaces;
            }

          } else {
            $classAttributes['inheritanceTree'] = array($classAttributes['inheritance']);
          }
        }

        $subclasses = $this->getSubclasses($classAttributes['name']);

        if (sizeof($subclasses)) {
          $classAttributes['subclasses'] = $subclasses;
        }
      }
    }
  }

  /**
   * @param string $searchClassName
   * @param array &$result
   * @return array
   */
  protected function getParentClassesInfo($searchClassName, array &$result = array())
  {
    foreach ($this->_pages as $page) {
      if (!isset($page['classes'])) {
        continue;
      }

      $classes = $page['classes'];

      foreach ($classes as $classId => $attributes) {
        if ($attributes['name'] == $searchClassName) {
          $array = array();
          $array['name'] = $searchClassName;

          if (isset($attributes['interfaces'])) {
            $array['interfaces'] = $attributes['interfaces'];
          }

          $result[] = $array;

          if (isset($attributes['inheritance'])) {
            $this->getParentClassesInfo($attributes['inheritance'], $result);
            break;
          }
        }
      }
    }

    return $result;
  }

  /**
   * @param string $className
   * @return array
   */
  protected function getSubclasses($className)
  {
    $subclasses = array();

    foreach ($this->_pages as $page) {
      if (!isset($page['classes'])) {
        continue;
      }

      foreach ($page['classes'] as $classId => $attributes) {
        if (isset($attributes['inheritance']) && $attributes['inheritance'] === $className) {
          $subclasses[] = $attributes['name'];
        }
      }
    }

    asort($subclasses);

    return $subclasses;
  }

  /**
   */
  protected function parseDependencyClassMembers()
  {
    $types = array('methods', 'properties');

    foreach ($this->_pages as $filePath => &$fileAttributes) {
      if (!isset($fileAttributes['classes'])) {
        continue;
      }

      $classes = &$fileAttributes['classes'];

      foreach ($classes as $classId => &$classAttributes) {
        $classAttributes['hasOverrideProperty'] = FALSE;
        $classAttributes['hasOverrideMethod'] = FALSE;
        $classAttributes['hasInheritanceProperty'] = FALSE;
        $classAttributes['hasInheritanceMethod'] = FALSE;

        // 対象クラスに親クラスが存在する場合
        if (isset($classAttributes['inheritanceTree'])) {
          // 親から子クラスの順に解析
          $inheritanceTree = array_reverse($classAttributes['inheritanceTree']);

          foreach ($inheritanceTree as $parentClassName) {
            // 親クラスがパッケージ外の場合は解析しない
            $findClassAttributes = NULL;

            foreach ($this->_pages as $page) {
              if (!isset($page['classes'])) {
                continue;
              }

              foreach ($page['classes'] as $name => $values) {
                if ($values['name'] === $parentClassName) {
                  $findClassAttributes = $values;
                }
              }
            }

            if ($findClassAttributes === NULL) {
              continue;
            }

            foreach ($types as $type) {
              if (!isset($findClassAttributes[$type])) {
                continue;
              }

              foreach ($findClassAttributes[$type] as $name => $values) {
                // プライベートプロパティ (メソッド) は子に継承しない
                if ($values['access'] === 'private') {
                  continue;
                }

                $values['define'] = $parentClassName;

                // 親の持つプロパティ、メソッドをオーバーライド
                // クラスが孫→子→親の継承関係にある場合、親の持つメソッドは孫、子から見て 'isInheritance' が TRUE となる
                // 孫が直接親のメソッドを継承している場合、'isOverride' が TRUE となる
                if (isset($classAttributes[$type][$name])) {
                  if (!isset($classAttributes[$type][$name]['isInheritance'])) {
                    $classAttributes[$type][$name]['isOverride'] = TRUE;

                    if ($type === 'methods') {
                      $classAttributes['hasOverrideMethod'] = TRUE;
                    } else {
                      $classAttributes['hasOverrideProperty'] = TRUE;
                    }

                    if (!isset($findClassAttributes[$type][$name]['define']) || $findClassAttributes[$type][$name]['isOverride']) {
                      $classAttributes[$type][$name]['define'] = $parentClassName;
                    }
                  }

                // 親の持つプロパティ、メソッドを子に継承
                } else {
                  $values['isInheritance'] = TRUE;
                  $classAttributes[$type][$name] = $values;

                  if ($type === 'methods') {
                    $classAttributes['hasInheritanceMethod'] = TRUE;
                  } else {
                    $classAttributes['hasInheritanceProperty'] = TRUE;
                  }
                }
              }
            }
          }
        }

        // 親クラスに存在しないプロパティ、メソッドを識別する
        foreach ($types as $type) {
          if (!isset($classAttributes[$type])) {
            continue;
          }

          foreach ($classAttributes[$type] as $name => $value) {
            if (!isset($classAttributes[$type][$name]['define'])) {
              $classAttributes[$type][$name]['define'] = $classAttributes['name'];
            }

            if (!isset($classAttributes[$type][$name]['isOverride'])) {
              $classAttributes[$type][$name]['isOverride'] = FALSE;
            }

            if (!isset($classAttributes[$type][$name]['isInheritance'])) {
              $classAttributes[$type][$name]['isInheritance'] = FALSE;
            }

            if (!$classAttributes[$type][$name]['isOverride'] && !$classAttributes[$type][$name]['isInheritance']) {
              $classAttributes[$type][$name]['isOwner'] = TRUE;
            } else {
              $classAttributes[$type][$name]['isOwner'] = FALSE;
            }
          }
        }
      }
    }
  }

  /**
   * @return string
   */
  protected function createIndexPage()
  {
    $view = $this->_view;
    $view->setTemplatePath($this->getLayoutTemplatePath());
    $view->setAttribute('contentTemplateName', 'index');
    $view->setAttribute('indexPath', $this->_indexPageRelativeIndexPath . 'index' . $this->_linkExtension);
    $view->setAttribute('relativeIndexPath', $this->_indexPageRelativeIndexPath);
    $view->setAttribute('relativeAPIPath', $this->_indexPageRelativeAPIPath);
    $view->setAttribute('title', $this->_title);
    $view->setAttribute('menus', $this->_summaries, FALSE);
    $view->setAttribute('document', $this->_documentHelper, FALSE);
    $contents = $view->fetch();
    $view->clear();

    return $contents;
  }

  /**
   * @return string
   */
  protected function createReferencePage()
  {
    $data = array();
    $view = $this->_view;
    $view->setTemplatePath($this->getLayoutTemplatePath());
    $view->setAttribute('indexPath', $this->_referencePageRelativeIndexPath . 'index' . $this->_linkExtension);
    $view->setAttribute('relativeIndexPath', $this->_referencePageRelativeIndexPath);
    $view->setAttribute('relativeAPIPath', $this->_referencePageRelativeAPIPath);
    $view->setAttribute('title', $this->_title);
    $view->setAttribute('menus', $this->_summaries, FALSE);
    $view->setAttribute('document', $this->_documentHelper, FALSE);

    foreach ($this->_pages as $filePath => $fileAttributes) {
      if (isset($fileAttributes['defines'])) {
        ksort($fileAttributes['defines']);
      }

      if (isset($fileAttributes['functions'])) {
        ksort($fileAttributes['functions']);

        foreach ($fileAttributes['functions'] as $name => $functionAttributes) {
          $this->_documentHelper->setFileId($fileAttributes['file']['name']);
          $view->setAttribute('file', $fileAttributes, FALSE);
          $view->setAttribute('contentTemplateName', 'function');
          $contents = $view->fetch();

          $path = $this->createReferencePath($fileAttributes['file']['package'], $fileAttributes['file']['name']);
          $data[$path] = $contents;
        }

        unset($fileAttributes);
      }

      if (isset($fileAttributes['classes'])) {
        foreach ($fileAttributes['classes'] as $name => $classAttributes) {
          if (isset($classAttributes['constants'])) {
            ksort($classAttributes['constants']);
          }

          if (isset($classAttributes['properties'])) {
            ksort($classAttributes['properties']);
          }

          if (isset($classAttributes['methods'])) {
            ksort($classAttributes['methods']);
          }

          $view->setAttribute('className', $classAttributes['name']);
          $view->setAttribute('class', $classAttributes, FALSE);

          $this->_documentHelper->setFileId($classAttributes['name']);
          $view->setAttribute('contentTemplateName', 'class');
          $contents = $view->fetch();

          $path = $this->createReferencePath($classAttributes['package'], $classAttributes['name']);
          $data[$path] = $contents;

          unset($classAttributes);
        }
      }
    }

    $view->clear();

    return $data;
  }

  /**
   * @param array $pages
   */
  public function setPages(array $pages)
  {
    $this->_pages = $pages;
  }

  /**
   * @return array
   */
  public function getPages()
  {
    return $this->_pages;
  }

  /**
   */
  public function write()
  {
    // メディアファイルのデプロイ
    $from = array();
    $from[] = $this->_templateDirectory . DIRECTORY_SEPARATOR . 'assets';

    $to = array();
    $to[] = $this->_outputDirectory . DIRECTORY_SEPARATOR . 'assets';

    $j = sizeof($from);

    for ($i = 0; $i < $j; $i++) {
      PHI_FileUtils::copy($from[$i], $to[$i], array('recursive' => TRUE));
    }

    // インデックスページの出力
    $writePath = $this->_outputDirectory
               . DIRECTORY_SEPARATOR
               . 'index'
               . $this->_fileExtension;
    PHI_FileUtils::writeFile($writePath, $this->_indexData);

    // API の出力
    foreach ($this->_referenceDataList as $path => $data) {
      PHI_FileUtils::writeFile($path, $data);
    }
  }
}
