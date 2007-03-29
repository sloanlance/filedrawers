<?php
# Msu Modified file for Upload Progress Bar 

echo "REQUEST filename = $_REQUEST[filename]\n";

/*
if ( !preg_match( "/^[a-f0-9]+$/", $_REQUEST['filename'] )) {
    echo "filename is not valid\n";
    exit();
}
*/
echo @file_get_contents( "/tmp/" . $_REQUEST['filename'] );

?>
