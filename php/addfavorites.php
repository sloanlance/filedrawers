<?php
/*
 * Copyright (c) 2005 Regents of The University of Michigan.
 * All Rights Reserved.  See COPYRIGHT.
 */

require_once( 'afs.php' );
require_once( 'favorites.php' );
require_once( 'smarty.custom.php' );

$fav    = new Favorites( $_GET['target'] );
$smarty = new Smarty_Template;
$favorites = $fav->getFavorites();

if ( ! empty ( $fav->errorMsg )) {
    $smarty->assign( 'loadCmds', rawurlencode( "alert(decode($fav->errorMsg));" ));
}

$smarty->assign( 'favorites', $favorites );
$smarty->assign( 'usedFavs', sizeof( $favorites ));
$smarty->assign( 'target', $fav->favoriteTarget );
$smarty->assign( 'maxFavs', 5 ); // The maximum number of favorite locations
$smarty->assign( 'cmd', ( isset( $_POST['cmd'] )) ? $_POST['cmd'] : '' );
$smarty->display( 'addFavorites.tpl' );
?>
