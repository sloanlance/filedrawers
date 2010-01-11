<?php
error_reporting(E_ALL);
//ini_set('display_errors', 1); 
//ini_set('log_errors', 0);

$execTimeStart = microtime(true);
if ( ! extension_loaded( 'posix' )) {
    dl( 'posix.so' );
}

if ( ! extension_loaded( 'json' )) {
    dl( 'json.so' );
}

if ( ! extension_loaded( 'filedrawers' )) {
    dl( 'filedrawers.so' );
}

define( 'FS_DEVICE', '/afs' );

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

require_once APPLICATION_PATH . '/library/Autoload.php';
require_once APPLICATION_PATH . '/library/Config.php';
require_once APPLICATION_PATH . '/library/Registry.php';


$config = Config::getInstance();
$config->readConfig(APPLICATION_PATH . '/configs/config.ini');

$front = Controller_Front::getInstance();
$front->dispatch();

unset($config, $front);
$execTimeEnd = microtime(true);
//error_log('Process time1: ' . ($execTimeEnd - $execTimeStart));
