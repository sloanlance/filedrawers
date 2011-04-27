<?php
$conf[ 'phpSettings' ][ 'display_startup_errors' ] = '1';
$conf[ 'phpSettings' ][ 'display_errors' ] = '1';
$conf[ 'phpSettings' ][ 'error_reporting' ] = '6143';

$conf[ 'filesystem' ][ 'default' ] = 'ifs';
$conf[ 'filesystem' ][ 'active' ] = array( 'ifs', 'local' );
$conf[ 'filesystem' ][ 'services' ][ 'ifs' ]              [ 'label' ] = 'IFS';
$conf[ 'filesystem' ][ 'services' ][ 'ifs' ]              [ 'forceAfsUserDir' ] = '/afs/umich.edu/user';
$conf[ 'filesystem' ][ 'services' ][ 'ifs' ]              [ 'root' ] = '/afs/';
$conf[ 'filesystem' ][ 'services' ][ 'local' ]            [ 'label' ] = 'Local';


$conf[ 'resources' ][ 'frontController' ][ 'params' ][ 'displayExceptions' ] = TRUE;

// $conf[ 'resources' ][ 'db' ][ 'params' ][ 'host' ]     = 'db-host';
// $conf[ 'resources' ][ 'db' ][ 'params' ][ 'username' ] = 'db-user';
// $conf[ 'resources' ][ 'db' ][ 'params' ][ 'password' ] = 'db-pass';
// $conf[ 'resources' ][ 'db' ][ 'params' ][ 'dbname' ]   = 'db-name';
