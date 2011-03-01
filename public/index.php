<?php
// PHP's basename function will not handle high-ASCII characters correctly
// without this being set.  Using Zend_Locale alone won't fix this issue
setlocale(LC_ALL, 'en_US.UTF8');

if ( ! extension_loaded( 'posix' )) {
    dl( 'posix.so' );
}

if ( ! extension_loaded( 'pdo' )) {
    dl( 'pdo.so' );
}

if ( ! extension_loaded( 'pdo_mysql' )) {
    dl( 'pdo_mysql.so' );
}

if ( ! extension_loaded( 'json' )) {
    dl( 'json.so' );
}

if ( ! extension_loaded( 'filedrawers' )) {
    dl( 'filedrawers.so' );
}


// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENVIRONMENT')
    || define('APPLICATION_ENVIRONMENT', (getenv('APPLICATION_ENVIRONMENT') ? getenv('APPLICATION_ENVIRONMENT') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENVIRONMENT,
    APPLICATION_PATH . '/configs/application.ini'
);

$application->bootstrap()
            ->run();
