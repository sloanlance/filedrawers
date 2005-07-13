<?php
/*
 * Copyright (c) 2005 Regents of The University of Michigan.
 * All Rights Reserved.  See COPYRIGHT.
 */

require_once( '../objects/afs.php' );
require_once( '../objects/favorites.php' );
require_once( '../smarty/smarty.custom.php' );

$fav    = new Favorites( $_GET['target'] );
$smarty = new Smarty_Template;
$smarty->assign( 'favorites', $fav->getFavorites());
$smarty->assign( 'target', $fav->favoriteTarget );
$smarty->display( 'renameFavorites.tpl' );
?>
