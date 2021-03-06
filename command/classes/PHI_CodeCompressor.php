<?php
class PHI_CodeCompressor
{
  public function __construct()
  {
    $this->_compiledDir = PHI_ROOT_DIR . '/libs.compiled';
  }

  public function execute($path = NULL)
  {
    $this->invoke($path);

    echo sprintf("Compression is complete. [%s]\n", $this->_compiledDir);
  }

  private function invoke($path)
  {
    if ($path === NULL) {
      $path = PHI_LIBS_DIR;
    }

    $fileList = scandir($path);

    foreach ($fileList as $file) {
      if ($file === '.' || $file === '..' || $file === '.svn') {
        continue;
      }

      $sourcePath = $path . '/' . $file;

      if (is_dir($sourcePath)) {
        $this->invoke($sourcePath);

      } else {
        $pathInfo = pathinfo($sourcePath);

        if (empty($pathInfo['extension']) || $pathInfo['extension'] !== 'php') {
          continue;
        }

        $storePath = $this->_compiledDir . substr($sourcePath, strlen(PHI_LIBS_DIR));
        $directory = dirname($storePath);

        if (!is_Dir($directory)) {
          PHI_FileUtils::createDirectory($directory);
        }

        printf("  compacting: [%s]\n", substr($sourcePath, strlen(PHI_LIBS_DIR) + 1));

        $source = $this->compress($sourcePath);
        file_put_contents($storePath, $source);
      }
    }
  }

  private function compress($path)
  {
    $source = file_get_contents($path);

    // インクルードファイルの読み込み
    $pattern = '/(require (PHI_(ROOT|LIBS)_DIR)\s*\.\s*\'([\w\/\.]+)\';)/';
    $matches = array();
    $buffer = NULL;

    if (preg_match_all($pattern, $source, $matches)) {
      $j = sizeof($matches[0]);
      $source = preg_replace($pattern, '', $source);

      for ($i = 0; $i < $j; $i++) {
        if (strcmp($matches[2][$i], 'PHI_ROOT_DIR') === 0) {
          $basePath = PHI_ROOT_DIR;
          $pos = strlen(PHI_ROOT_DIR);

        } else if (strcmp($matches[2][$i], 'PHI_LIBS_DIR') === 0) {
          $basePath = PHI_LIBS_DIR;
          $pos = strlen(PHI_LIBS_DIR);
        }

        $path = $basePath . $matches[4][$i];
        printf("    include: [%s]\n", substr($path, $pos + 1));
        $buffer .= file_get_contents($path);
      }

      $buffer = str_replace("<?php\n", '', $buffer);
      $buffer = str_replace("?" . ">", '', $buffer);

      $source .= $buffer;
    }

    // 文字列の先頭の空白
    $source = preg_replace('/^\s+/m', '', $source);

    // コメント
    $source = preg_replace('/^\/\*.*\n/m', '', $source);
    $source = preg_replace('/^\*.*\n/m', '', $source);
    $source = preg_replace('/^\/\/[^\n]+\n/m', '', $source);
    $source = preg_replace('/^#.*\n/m', '', $source);
    $source = preg_replace('/\/\/[^"\'\n]*\n/m', '', $source); // 行末コメント (コメント中に ', " が含まれていた場合は未対応)
    $source = preg_replace('/#[^"\'\n]*\n/', '', $source); // 行末コメント (コメント中に ', " が含まれていた場合は未対応)

    // 空白行
    $source = preg_replace('/^\s*\n/m', '', $source);

    // 括弧
    $source = preg_replace('/\n?{\n/', "{", $source);
    $source = preg_replace('/}\n/', "}", $source);
    $source = preg_replace('/\)\n/', ")", $source);

    $source = PHI_StringUtils::excludeReplace(' {', '{', $source, '\'');
    $source = PHI_StringUtils::excludeReplace('} ', '}', $source, '\'');
    $source = PHI_StringUtils::excludeReplace(' (', '(', $source, '\'');
    $source = PHI_StringUtils::excludeReplace(') ', ')', $source, '\'');

    // 代数演算子
    $source = str_replace(' + ', '+', $source);
    $source = str_replace(' - ', '-', $source);
    $source = str_replace(' * ', '*', $source);
    $source = str_replace(' / ', '/', $source);
    $source = str_replace(' % ', '%', $source);

    // 代入演算子
    $source = str_replace(' = ', '=', $source);

    $source = str_replace(' == ', '==', $source);
    $source = str_replace(' === ', '===', $source);
    $source = str_replace(' != ', '!=', $source);
    $source = str_replace(' <> ', '<>', $source);
    $source = str_replace(' !== ', '!==', $source);
    $source = str_replace(' < ', '<', $source);
    $source = str_replace(' > ', '>', $source);
    $source = str_replace(' <= ', '<=', $source);
    $source = str_replace(' >= ', '>=', $source);

    // ビット演算子
    $source = str_replace(' & ', '&', $source);
    $source = str_replace(' | ', '|', $source);
    $source = str_replace(' ^ ', '^', $source);
    $source = str_replace(' ~ ', '~', $source);
    $source = str_replace(' << ', '<<', $source);
    $source = str_replace(' >> ', '>>', $source);

    // 論理演算子
    #$source = PHI_StringUtils::excludeReplace(' and ', 'and', $source, '\'');
    #$source = PHI_StringUtils::excludeReplace(' or ', 'or', $source, '\'');
    #$source = PHI_StringUtils::excludeReplace(' xor ', 'xor', $source, '\'');
    $source = PHI_StringUtils::excludeReplace(' && ', '&&', $source, '\'');
    $source = PHI_StringUtils::excludeReplace(' || ', '||', $source, '\'');

    // 文字列演算子
    $source = str_replace(' . ', '.', $source);
    $source = str_replace("\n.", '.', $source);

    // その他
    $source = str_replace(', ', ',', $source);
    $source = str_replace(",\n", ',', $source);
    $source = str_replace(";\n", ';', $source);

    // PHP タグ
    $source = str_replace("<?php\n", '<?php ', $source);

    // 1行にする
    $source = preg_replace("/$\n/m", '', $source);

    return $source;
  }
}
