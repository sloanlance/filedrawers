<?php
/* $Revision: $
 *
 * Copyright (c) 2009 Regents of the University of Michigan.
 * All rights reserved.
 */

class FavoritesController extends Controller_Core {
    protected $filesystem;
    protected $favoritesPath;
    protected $errorMsg;

    public function init()
    {
        $config = Config::getInstance();
        $homedir = null;
        $this->filesystem = Registry::getInstance()->filesystem;

        if (isset($config->filesystem['homedir'])) {
            $homedir = $config->filesystem['homedir'];
        }
        else {
            $userInfo = posix_getpwnam(Auth::getInstance()->getUsername());

            if ( ! empty($userInfo['dir']) && is_dir($userInfo['dir'])) {
                $homedir = $userInfo['dir'];
            }
        }

        $this->favoritesPath = $homedir . '/Favorites';

        if ( ! $this->filesystem->linkSafeFileExists($this->favoritesPath)) {
            $this->filesystem->createDirectory($this->favoritesPath);
        }

        $this->filesystem->addListHelper(array('FavoritesController', 'setSymLink'));
    }


    public function indexAction()
    {
        $this->view->favorites = $this->filesystem->listDirectory($this->favoritesPath);
    }


    private function addAction()
    {
        if ( ! symlink( $this->favoriteTarget,
                basename( $this->selectedItems ))) {
            $this->errorMsg = "Unable to add the favorite location.";
            @chdir( $this->startCWD );
            return false;
        }

        @chdir( $this->startCWD );
        return true;
    }


    private function renameAction()
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


    public function removeAction()
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


    public static function setSymLink(&$row)
    {
        if (strpos($row['filename'], '.') !== 0) {
            $link = @readlink($row['filename']);
            $row['target'] = $link;
        }
    }


    // Sets the location that a Favorites symlink points to
    protected function setFavoriteTarget( $target='' )
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
}

