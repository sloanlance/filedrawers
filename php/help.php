<?php
/*
 * Copyright (c) 2005 Regents of The University of Michigan.
 * All Rights Reserved.  See COPYRIGHT.
 */

require_once( 'version.php' );
require_once( 'config.php' );
require_once( 'smarty.custom.php' );

$smarty = new Smarty_Template;
$smarty->assign( 'filedrawers_title', 'help');
$smarty->assign( 'redirect', true );
$smarty->assign( 'stylesheets', array( '/css/fileman.css'));
$smarty->assign( 'filedrawers_version', $filedrawers_version);
$smarty->assign( 'secure_service_url', $secure_service_url);
$smarty->display( 'help.tpl' );
?>
