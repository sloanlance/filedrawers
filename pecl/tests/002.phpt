--TEST--
Check if filedrawers_rename works
--SKIPIF--
<?php if (0) print "skip"; ?>
--FILE--
<?php
 if ( !extension_loaded( "filedrawers" )) {
    echo "filedrawers module not loaded";
    exit( 1 );
 }
 $filea = tempnam( './tests', 'filea_' );
 $fileb = tempnam( './tests', 'fileb_' );
 if ( filedrawers_rename( $filea, $fileb, '.' ) == FALSE ) {
    echo "filedrawers_rename failed";
    unlink( $filea );
    unlink( $fileb );
    exit( 1 );
 }
 unlink( $fileb );
 echo "filedrawers_rename succeeded";
?>
--EXPECT--
filedrawers_rename succeeded
