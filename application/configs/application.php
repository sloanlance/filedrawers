<?php // avoid making changes to this file, instead use APPLICATION_PATH/configs/local.php

$conf[ 'phpSettings' ][ 'display_startup_errors' ] = '0';
$conf[ 'phpSettings' ][ 'display_errors' ]         = '0';

$conf[ 'includePaths' ][ 'library' ] = APPLICATION_PATH .'/library';

$conf[ 'bootstrap' ][ 'path' ] = APPLICATION_PATH .'/Bootstrap.php';
$conf[ 'bootstrap' ][ 'class' ] = 'Bootstrap';

$conf[ 'resources' ][ 'frontController' ][ 'moduledirectory' ] = APPLICATION_PATH .'/modules';
$conf[ 'resources' ][ 'frontController' ][ 'defaultmodule' ] = 'webapp';
$conf[ 'resources' ][ 'frontController' ][ 'plugins' ][ 'cosign' ] = 'CoSign_Controller_Plugin_CoSign';

$conf[ 'resources' ][ 'view' ] = array ( '' );
$conf[ 'resources' ][ 'view' ][ 'doctype' ] = 'XHTML1_TRANSITIONAL';
$conf[ 'resources' ][ 'layout' ][ 'layout' ] = 'layout';
$conf[ 'resources' ][ 'layout' ][ 'layoutpath' ] = APPLICATION_PATH .'/layouts/scripts';
$conf[ 'resources' ][ 'frontController' ][ 'params' ][ 'displayExceptions' ] = FALSE;

$conf[ 'autoloadernamespaces' ][ 'filedrawers' ] = 'Filedrawers_';
$conf[ 'autoloadernamespaces' ][ 'cosign' ]      = 'CoSign_';
$conf[ 'autoloadernamespaces' ][ 'controller' ]  = 'Controller_';

$conf[ 'filedrawers' ][ 'version' ] = '0.5.0';

$conf[ 'filesystem' ][ 'forceAfsUserDir' ] = '/afs/umich.edu/user';
$conf[ 'filesystem' ][ 'root' ] = '/afs/';
$conf[ 'filesystem' ][ 'services' ][ 'default' ] = 'afs';
$conf[ 'filesystem' ][ 'services' ][ 'active' ][ 'afs' ] = 'AFS';

$conf[ 'mime' ][ 'imagesPath' ] = '/usr/local/projects/mfile/images/mime/small';

$conf[ 'afs' ][ 'utilitiesPath' ] = '/usr/bin';

// this must be specified before the saveHandler class, set db params in local.php
$conf[ 'resources' ][ 'db' ][ 'adapter' ] = 'PDO_MYSQL';

$conf[ 'resources' ][ 'session' ][ 'saveHandler' ][ 'class' ] = 'Zend_Session_SaveHandler_DbTable';
$conf[ 'resources' ][ 'session' ][ 'saveHandler' ][ 'options' ][ 'name' ] = 'session';
$conf[ 'resources' ][ 'session' ][ 'saveHandler' ][ 'options' ][ 'primary' ] = 'id';
$conf[ 'resources' ][ 'session' ][ 'saveHandler' ][ 'options' ][ 'modifiedColumn' ] = 'modified';
$conf[ 'resources' ][ 'session' ][ 'saveHandler' ][ 'options' ][ 'dataColumn' ] = 'data';
$conf[ 'resources' ][ 'session' ][ 'saveHandler' ][ 'options' ][ 'lifetimeColumn' ] = 'lifetime';
