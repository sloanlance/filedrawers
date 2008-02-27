<?php
/*
 * Copyright (c) 2005 - 2008 Regents of The University of Michigan.
 * All Rights Reserved.  See COPYRIGHT.
 */

require_once( 'libdrawers.php' );

if ( !extension_loaded( 'posix' )) {
    if ( !dl( 'posix.so' )) {
        error_log("Couldn't load necessary posix function");
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
        $this->startCWD = getcwd();
        $this->afsStat  = stat('/afs/');

        session_start();
        if ( !isset( $_SESSION['formKey'] )) {
            $_SESSION['formKey'] = md5( uniqid( rand(), true ));
        }

        $this->formKey = $_SESSION['formKey'];
        $this->setFavoritesStore();
        $this->setFavoriteTarget( $target );
        $this->processFavoritesCommand();
    }


    public function processFavoritesCommand()
    {
        if ( !isset( $_POST['command'] ) || $this->formKey != $_POST['formKey'] ) {
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

        if ( ! $this->makePathAFSlocal( $this->path )) {
            $this->errorMsg - 'Unable to set your Favorites store.';
            return false;
        }

        if ( ! $this->linkSafeFileExists( 'Favorites' )) {
            $this->selectedItems = 'Favorites';
            $this->createFolder();
        }

        $this->setPath( getBasePath( $this->uniqname ) . '/Favorites' );

        @chdir( $this->startCWD );
        return true;
    }


    private function addFavorite()
    {
        if ( ! $this->makePathAFSlocal( $this->path )) {
            return false;
        }

        if ( ! symlink( $this->favoriteTarget,
                basename( $this->selectedItems ))) {
            $this->errorMsg = "Unable to add the favorite location.";
            @chdir( $this->startCWD );
            return false;
        }

        @chdir( $this->startCWD );
        return true;
    }


    private function renameFavorite()
    {
        if ( ! $this->makePathAFSlocal( $this->path )) {
            return false;
        }

        $renames = $this->selectedItems;

        foreach ( $renames as $oldName => $newName ) {
            $oldName = basename( $oldName );
            $newName = basename( $newName );

            if ( ! $target = @readlink( $oldName )) {
                continue;
            }

            if ( ! unlink( $oldName )) {
                $this->errorMsg = 'Unable to rename the favorite(s).';
                @chdir( $this->startCWD );
                return false;
            }

            if ( ! symlink( $target, $newName )) {
                $this->errorMsg = 'Unable to rename the favorite(s).';
                @chdir( $this->startCWD );
                return false;
            }
        }

        @chdir( $this->startCWD );
        return true;
    }


    // Sets the location that a Favorites symlink points to
    public function setFavoriteTarget( $target='' )
    {
        $target = html_entity_decode( urldecode( $target ), ENT_QUOTES );
        
        if ( empty( $target ) || ! $this->makePathAFSlocal( $target )) {
            $this->errorMsg       = '';
            $this->favoriteTarget = getBasePath( $this->uniqname );
            @chdir( $this->startCWD );
            return false;
        } else {
            $this->favoriteTarget = $target;
        }

        @chdir( $this->startCWD );
        return true;
    }


    public function getFavorites()
    {
        $favorites = '';

        // Open the path and read its contents
        if ( !@is_dir( $this->path )) {
            $this->errorMsg = "Unable to view $this->path.";
            return false;
        }

        if ( !$this->makePathAFSlocal( $this->path )) {
            $this->errorMsg = "Unable to view: $this->path.";
            return false;
        }

        if ( !$dh = @opendir( '.' )) {
            $this->errorMsg = "Unable to view $this->path.";
            @chdir( $this->startCWD );
            return false;
        }

        while ( $filename = readdir( $dh )) {
            if ( strpos( $filename, '.' ) !== 0 ) {
                $link = @readlink( $filename );
                $favorites[$filename] = $link;
            }
        }

        closedir( $dh );

        @chdir( $this->startCWD );

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

        if ( !$this->makePathAFSlocal( $this->path )) {
            return false;
        }

        foreach ( $this->selectedItems as $link ) {
            $link = basename( $link );

            if ( ! is_link( $link ) || ! unlink( $link )) {
                $this->errorMsg = "Unable to remove: $link.";
                @chdir( $this->startCWD );
                return false;
            }
        }

        @chdir( $this->startCWD );
        return true;
    }
}
?>
