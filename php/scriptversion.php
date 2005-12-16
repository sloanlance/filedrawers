<?php
/*
 * Copyright (c) 2005 Regents of The University of Michigan.
 * All Rights Reserved.  See COPYRIGHT.
 */

require_once( 'smarty.custom.php' );

$smarty = new Smarty_Template;
$smarty->assign( 'browser_id', $_SERVER['HTTP_USER_AGENT'] );
$smarty->assign( 'filedrawers_title', 'javascript version error');
$smarty->assign( 'redirect', true );
$smarty->assign( 'stylesheets', array( '/css/fileman.css'));
$smarty->display( 'scriptversion.tpl' );
?>
