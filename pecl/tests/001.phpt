--TEST--
Check for filedrawers module
--SKIPIF--
<?php if (!extension_load("filedrawers")) print "skip"; ?>
--FILE--
<?php
 echo "filedrawers module is available";
?>
--EXPECT--
filedrawers module is available
