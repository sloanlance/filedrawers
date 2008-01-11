<?php
/*
 * Copyright (c) 2008 Regents of The University of Michigan.
 * All Rights Reserved.  See COPYRIGHT.
 */

require_once( 'smarty.custom.php' );

$smarty = new Smarty_Template;
$smarty->assign( 'filedrawers_title', 'missing home directory');
$smarty->assign( 'redirect', true );
$smarty->assign( 'stylesheets', array( '/css/fileman.css'));
$smarty->display( 'missinghomedir.tpl' );
?>
