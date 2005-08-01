<?php
/*
 * Copyright (c) 2005 Regents of The University of Michigan.
 * All Rights Reserved.  See COPYRIGHT.
 */

require_once( '../lib/version.php' );
require_once( '../smarty/smarty.custom.php' );

$smarty = new Smarty_Template;
$smarty->assign( 'trouser_title', 'help');
$smarty->assign( 'redirect', true );
$smarty->assign( 'stylesheets', array( '/fileman.css'));
$smarty->assign( 'filedrawers_version', $filedrawers_version);
$smarty->display( 'help.tpl' );
?>
