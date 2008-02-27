--TEST--
Check if filedrawers_unlink works
--SKIPIF--
<?php if (0) print "skip"; ?>
--FILE--
<?php
 if ( !extension_loaded( "filedrawers" )) {
    echo "filedrawers module not loaded";
    exit( 1 );
 }
 $file = tempnam( './tests', 'file_' );
 $sympath = './tests/symlink';
 if ( symlink( $file, $sympath ) == FALSE ) {
    echo "symlink failed";
    unlink( $file );
    exit( 1 );
 }
 if ( filedrawers_unlink( $sympath, '.' ) == FALSE ) {
    echo "filedrawers_rename failed";
    unlink( $file );
    unlink( $sympath );
    exit( 1 );
 }
 unlink( $file );
 echo "filedrawers_unlink succeeded";
?>
--EXPECT--
filedrawers_unlink succeeded
