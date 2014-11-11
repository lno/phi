<?php
ini_set('display_errors', 0);

//define('PHI_ROOT_DIR', '{%PHI_ROOT_DIR%}');
define('PHI_ROOT_DIR', dirname(dirname(__DIR__)).'/phi');
define('PHI_LIBS_DIR', PHI_ROOT_DIR . '/libs');
define('APP_ROOT_DIR', dirname(__DIR__));

require PHI_LIBS_DIR . '/kernel/loader/PHI_BootLoader.php';

