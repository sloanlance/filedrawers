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
        $homedir  = $this->filesystem->getHomeDir();
        $this->filesystem = Registry::getInstance()->filesystem;


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


    public function addAction()
    {
        if ( ! symlink( $this->favoriteTarget,
                basename( $this->selectedItems ))) {
            $this->errorMsg = "Unable to add the favorite location.";
        }
    }


    public function renameAction()
    {
        $this->view->setNoRender();

        $renames = $_POST['renames'];

        foreach ($renames as $oldName => $newName) {
            if ($oldName == $newName) {
                continue;
            }

            $oldPath = $this->favoritesPath . '/' . $oldName;
            $newPath = $this->favoritesPath . '/' . $newName;

            echo $oldPath . "\n" . $newPath . "\n";

            //$this->filesystem->rename($oldPath, $newPath);
        }

        echo json_encode($this->filesystem->listDirectory($this->favoritesPath));
    }


    public function deleteAction()
    {
        $this->view->setNoRender();

        $path = Router::getInstance()->getFSpath();
        $this->filesystem->deleteFiles($path, $_POST['deletes']);
    }


    public static function setSymLink(&$row)
    {
        if (strpos($row['filename'], '.') === 0 || ! is_link($row['filename'])) {
            $row = false;
        } else {
            $link = @readlink($row['filename']);
            $row['target'] = $link;
        }
    }
}

