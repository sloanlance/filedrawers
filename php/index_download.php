<?php
/*
 * Copyright (c) 2004 Regents of The University of Michigan.
 * All Rights Reserved.  See COPYRIGHT.
 */

require_once('../lib/afs.php');

if (!extension_loaded('fileinfo')) {
    dl('fileinfo.' . PHP_SHLIB_SUFFIX);
}

if (!extension_loaded('fileinfo')) {
    die("fileinfo extension is not avaliable, please compile it.\n");
}

$download = new Afs();
$download->setPath($_GET['path']);
$res = finfo_open(FILEINFO_MIME);

header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

header("Content-Type: " . finfo_file($res, $download->path) );
header("Content-Disposition: attachment; filename=".basename($download->path) );

header("Content-Description: File Transfer");
@readfile($download->path);
finfo_close($res);
?>
