<?php
/*
 * Copyright (c) 2005 - 2009 Regents of The University of Michigan.
 * All Rights Reserved.  See COPYRIGHT.
 */

/* IE does not accept cache control headers for downloads over SSL
 * For more information, see: http://support.microsoft.com/kb/323308
 * private_no_expire prevents filedrawers from sending the headers
 */
session_cache_limiter( 'private_no_expire' );
require_once( 'config.php' );
require_once( 'afs.php' );

$download = new Afs( $_GET['path'] );
// replace " with \" to escape quoted " in html header
$basepath = '"' . preg_replace('/"/', '\"', basename($download->path)) . '"';

header( 'Content-Description: File Transfer' ); 
header( 'Content-Type: application/force-download' ); 
header( 'Content-Length: ' . filesize( $download->path )); 
header( "Content-Disposition: attachment; filename=$basepath;");
$download->readfile();

