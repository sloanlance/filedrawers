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

        $this->view->path  = $homedir;
        $this->view->files = $this->filesystem->listDirectory($homedir);
    }


    public function listAction()
    {
        $path = Router::getInstance()->getFSpath();
        $this->view->path  = $path;
        $this->view->files = $this->filesystem->listDirectory($path);
    }

    public function ajaxlistAction()
    {
        $path = Router::getInstance()->getFSpath();
        $this->view->path  = $path;
        $this->view->files = $this->filesystem->listDirectory($path);
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
        $path = Router::getInstance()->getFSpath();

        $oldPath = $path . '/' . $_POST['oldName'];
        $newPath = $path . '/' . $_POST['newName'];

        $this->view->setNoRender();

        if ($this->filesystem->rename($oldPath, $newPath)) {
            echo json_encode(array('status' => 'success', 'message' => $this->filesystem->notifyMsg));
        } else {
            echo json_encode(array('status' => 'fail', 'message' => $this->filesystem->errorMsg));
        }
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
        $path = Router::getInstance()->getFSpath();

        if ($this->filesystem->deleteFiles($path, $_POST['files'])) {
            $this->view->response = array('status' => 'success', 'message' => $this->filesystem->notifyMsg);
        } else {
            $this->view->response = array('status' => 'fail', 'message' => $this->filesystem->errorMsg);
        }
    }

    public function mkdirAction()
    {
        $this->view->setNoRender();
        $path = Router::getInstance()->getFSpath();

        if ($this->filesystem->createDirectory($path, $_POST['folderName'])) {
            echo json_encode(array('status' => 'success', 'message' => $this->filesystem->notifyMsg));
        } else {
            echo json_encode(array('status' => 'fail', 'message' => $this->filesystem->errorMsg));
        }
    }

    public function setPermissionsAction()
    {
        //
    }
    
    public function uploadStatusAction()
    {
        //
    }
    
    public function testAction()
    {}
}

