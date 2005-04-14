<?php
/*
 * Copyright (c) 2005 Regents of The University of Michigan.
 * All Rights Reserved.  See COPYRIGHT.
 */

require_once( '../../objects/afs.php' );

$download = new Afs( $_GET['path'] );

header( 'Content-Description: File Transfer' ); 
header( 'Content-Type: application/force-download' ); 
header( 'Content-Length: ' . filesize( $download->path )); 
header( 'Content-Disposition: attachment; filename=' . basename( $download->path )); 
readfile( $download->path );
?>

