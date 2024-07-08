<?php
ini_set('date.timezone', 'PRC');
ini_set('default_charset', 'utf-8');
define('BASE_VERSION', '1.0');
define('BASE_BUILD', '20230902');
define('BASE_ROOT', str_replace('\\', '/', substr(__DIR__, 0, -14)));
define('BASE_PATH', str_ireplace($_SERVER['DOCUMENT_ROOT'], '', BASE_ROOT));
define('BASE_CHARSET', ini_get('default_charset'));
define('BASE_DBCHARSET', str_replace('-', '', BASE_CHARSET));
define('BASE_DBHOST', '127.0.0.1');
define('BASE_DBPORT', '3306');
define('BASE_DBUSER', 'root');
define('BASE_DBPW', 'windowsX999');
define('BASE_DBNAME', 'test');
define('BASE_DBTABLEPRE', 'pre_');
?>