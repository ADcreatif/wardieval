<?php

define('_ROOT_', str_replace("index.php", "", $_SERVER['SCRIPT_NAME']));
define('_ROOT_CSS_', _ROOT_ . 'views/css/');
define('_ROOT_JS_', _ROOT_ . 'views/js/');
define('_ROOT_IMG_', _ROOT_ . 'views/img/');
define('_DB_HOST_', '127.0.0.1');
define('_DB_USER_', 'root');
define('_DB_NAME_', 'wardieval');
define('_DB_PASS_', '');
define('_DEBUG_', 1);
define('_MAINTENANCE_', 0);
