<?php
/*
 * Copyright (c) 2008 Regents of The University of Michigan.
 * All Rights Reserved.  See COPYRIGHT.
 */

require_once( 'afs.php' );
require_once( 'favorites.php' );
require_once( 'smarty.custom.php' );

$fav    = new Favorites( $_GET['target'] );
$smarty = new Smarty_Template;
$smarty->assign( 'favorites', $fav->getFavorites());
$smarty->assign( 'formKey', $fav->formKey );
$smarty->assign( 'target', $fav->favoriteTarget );
$smarty->display( 'deleteFavorites.tpl' );
?>
