<?php
/*
 * Copyright (c) 2005 Regents of The University of Michigan.
 * All Rights Reserved.  See COPYRIGHT.
 */

if ( !extension_loaded( 'posix' )) {
    if ( !dl( 'posix.so' )) {
        error_log("mFile: Couldn't load necessary posix function");
        echo "<p>Couldn't load necessary posix function</p>\n";
        exit( 1 );
    }
}

// remember this class needs to have $this->path set
class Favorites extends Afs
{
    public $favoriteTarget;

    public function __construct( $target="" )
    {
        $this->uniqname = $_SERVER['REMOTE_USER'];
        $this->setFavoritesStore();
        $this->setFavoriteTarget( $target );
        $this->processFavoritesCommand();
    }


    public function processFavoritesCommand()
    {
        if ( ! isset( $_POST['command'] )) {
            return false;
        }

        $this->setSelectedItems();

        switch ( $_POST['command'] ) {
            case 'Add':
                $this->addFavorite();
                break;
            case 'Rename':
                $this->renameFavorite();
                break;
            case 'Delete':
                $this->removeFavorite();
                break;
            default:
                break;
        }
    }


    // The location inside a user's afs space to store the favorites symlinks
    private function setFavoritesStore()
    {
        $this->setPath( getBasePath( $this->uniqname ));
        if ( ! $this->folderExists( $this->path . '/Favorites' )) {
            $this->selectedItems = 'Favorites';
            $this->createFolder();
        }

        $this->setPath( getBasePath( $this->uniqname )
          . '/Favorites' );
    }

    private function addFavorite()
    {
        if ( ! symlink( $this->favoriteTarget, $this->path . '/'
          . $this->selectedItems )) {
            $fav->errorMsg = "That Favorite Location name is not valid.";
        }
    }

    private function renameFavorite()
    {
        $renames = $this->selectedItems;

        foreach ( $renames as $oldName => $newName ) {
            $this->selectedItems = $oldName;
            $this->newName = $newName;
            $this->afsRename();
        }
    }

    // Sets the location that a Favorites symlink points to
    public function setFavoriteTarget( $target='' )
    {
        $target = html_entity_decode( urldecode( $target ), ENT_QUOTES );

        if ( $target ) {
            if ( ! file_exists( $target )) {
                $this->errorMsg = "The specified path does not exist. ($target)";
            } else {
                $this->favoriteTarget = $this->pathSecurity( $target );
            }
        }

        if ( ! $this->favoriteTarget ) {
            $this->favoriteTarget = getBasePath($this->uniqname);
        }
    }

    public function getFavorites()
    {
        $favorites = '';

        // Open the path and read its contents
        if ( !@is_dir( $this->path )) {
            $this->errorMsg = "Unable to view $this->path.";
            return false;
        }

        if ( !$dh = @opendir( $this->path )) {
            $this->errorMsg = "Unable to view $this->path.";
            return false;
        }

        while ( $filename = readdir( $dh )) {
            if ( strpos( $filename, '.' ) !== 0 ) {
                $link = @readlink( $this->path . '/' . $filename );
                $favorites[$filename] = $link;
            }
        }

        closedir( $dh );

        if ( $favorites ) {
            return $favorites;
        }
    }

    // Delete specified files
    public function removeFavorite()
    {
        if ( ! $this->selectedItems ) {
            return false;
        }

        foreach ( $this->selectedItems as $link ) {
            if ( ! is_link( $this->path . '/' . $link ) ||
              ! @unlink( $this->path . '/' . $link )) {
                $this->errorMsg = "Unable to delete $link.";
                                return false;
            }
        }
    }
}
?>
