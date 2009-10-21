<?php
/* $Revision: $
 *
 * Copyright (c) 2009 Regents of the University of Michigan.
 * All rights reserved.
 */

class FiledrawersController extends Controller_Core {
    protected $filesystem;

    public function init()
    {
        $this->filesystem = Registry::getInstance()->filesystem;
    }


    public function indexAction()
    {
        $config = Config::getInstance();
        $homedir = null;

        if (isset($config->filesystem['homedir'])) {
            $homedir = $config->filesystem['homedir'];
        } else {
            $userInfo = posix_getpwnam(Auth::getInstance()->getUsername());

            if ( ! empty($userInfo['dir']) && is_dir($userInfo['dir'])) {
                $homedir = $userInfo['dir'];
            }
        }

         $this->view->files = $this->filesystem->listDirectory($homedir);
    }


    public function listAction()
    {
        $this->view->files =
                $this->filesystem->listDirectory(Router::getInstance()->getFSpath());
    }


    public function downloadAction()
    {
        $this->view->setNoRender();

        $name = '"' . str_replace('"', '\"',
                basename(Router::getInstance()->getFSpath())) . '"';

        header( 'Content-Description: File Transfer' );
        header( 'Content-Type: application/force-download' );
        header( 'Content-Length: ' . $this->filesystem->getSize(Router::getInstance()->getFSpath()));
        header( "Content-Disposition: attachment; filename=$name;");
        $this->filesystem->readfile(Router::getInstance()->getFSpath());
    }


    public function renameAction()
    {
        //
    }
    
    public function moveAction()
    {
        //
    }
    
    public function copyAction()
    {
        //
    }
    
    public function deleteAction()
    {
        //
    }

    public function setPermissionsAction()
    {
        //
    }
    
    public function uploadStatusAction()
    {
        //
    }
}

