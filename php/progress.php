<?php
/*
 * Copyright (c) 2005 Regents of The University of Michigan.
 * All Rights Reserved.  See COPYRIGHT.
 */

if ( preg_match( "/[^a-f0-9]/", $_GET['sessionid'] ) !== 0 ) {
    exit();
}

$rawUpload      = @file_get_contents( "/tmp/" . $_GET['sessionid'] );
$upload         = explode( ':', $rawUpload );
$totalBytes     = ( isset( $upload[1] )) ? (int) $upload[1] : 0;
$bytesUploaded  = ( isset( $upload[2] )) ? (int) $upload[2] : 0;
$formatUploaded = formatBytes( $bytesUploaded );
$formatTotal    = formatBytes( $totalBytes );

if ( $bytesUploaded > 0 && $totalBytes > 0 ) {
    $percent = ( $bytesUploaded / $totalBytes ) * 100;
} else {
    $percent = 0;
}

$formatPercent = round( $percent );

// Display file sizes in a user-friendly format
function formatBytes( $bytes ) {
    if ( $bytes >= 1073741824 ) {
        return round( $bytes / 1073741824, 2 ) . ' GB';
    } else if ( $bytes >= 1048576 ) {
        return round( $bytes / 1048576, 2 ) . ' MB';
    } else if ( $bytes >= 1024 ) {
        return round( $bytes / 1024, 2 ) . ' KB';
    } else if ( $bytes > 0 && $bytes < 1024 ) {
        return $bytes . ' Bytes';
    } else {
        return "0 Bytes";
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta http-equiv="refresh" content="1; url="progress.php?sessionid=<?= $_GET['sessionid']; ?>" />
<title>Untitled Document</title>
<style type="text/css">
<!--
body {
    font-family: Verdana, Arial, Helvetica, Geneva, sans-serif;
    font-size: 12px;
    background-color: #ffffcc;
    color: #666666;
}
-->
</style>
</head>
<body>
<div style="width: 85%; height: 20px; border: 1px solid #cccccc; padding: 1px; margin: 15px auto 15px auto;">
    <div style="width: <?= $percent; ?>%; height: 20px; background-color: #336699;"></div>
    <table width="100%">
        <tr>
            <td>Uploaded:
                <?= $formatUploaded . ' / ' . $formatTotal; ?></td>
            <td align="right"><?= $formatPercent; ?>
                %</td>
        </tr>
    </table>
</div>
</body>
</html>
