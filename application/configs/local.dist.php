<?php
$conf[ 'phpSettings' ][ 'display_startup_errors' ] = '1';
$conf[ 'phpSettings' ][ 'display_errors' ] = '1';
$conf[ 'phpSettings' ][ 'error_reporting' ] = '6143';


$conf[ 'filesystem' ][ 'services' ][ 'default' ] = 'ifs';
unset( $conf[ 'filesystem' ][ 'services' ][ 'active' ] );
$conf[ 'filesystem' ][ 'services' ][ 'active' ][ 'ifs' ] = 'IFS';
$conf[ 'filesystem' ][ 'services' ][ 'active' ][ 'mainstreamStorage' ] = 'Mainstream Storage';

$conf[ 'resources' ][ 'frontController' ][ 'params' ][ 'displayExceptions' ] = TRUE;

// $conf[ 'resources' ][ 'db' ][ 'params' ][ 'host' ]     = 'db-host';
// $conf[ 'resources' ][ 'db' ][ 'params' ][ 'username' ] = 'db-user';
// $conf[ 'resources' ][ 'db' ][ 'params' ][ 'password' ] = 'db-pass';
// $conf[ 'resources' ][ 'db' ][ 'params' ][ 'dbname' ]   = 'db-name';
