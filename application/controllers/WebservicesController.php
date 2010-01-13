<?php
/* $Revision: $
 *
 * Copyright (c) 2009 Regents of the University of Michigan.
 * All rights reserved.
 */

class WebservicesController extends Controller_Core {
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

    private function hasArg($name){
        return isset($_GET[$name]);
    }
    private function getArg($name, $regex){
        // TODO: Security?! HAHAHAHAHAHA
        return @$_GET[$name];
    }

    public function setFriendlyValues(&$row)
    {
        $row['size'] = WebservicesController::bytes($row['size']);
        if(!empty($row['modTime'])){
            $row['modTime'] = date("m/d/Y h:i:s",$row['modTime']);
        }
        return true;
    }

    //http://php.net/manual/en/function.number-format.php
    function bytes($a) {
        $unim = array("B","KB","MB","GB","TB","PB");
        $c = 0;
        while ($a>=1024) {
            $c++;
            $a = $a/1024;
        }
        return number_format($a,($c ? 2 : 0),".",",")." ".$unim[$c];
    }

    public function listAction()
    {
        $path = Router::getInstance()->getFSpath();
        $this->view->path  = $path;

        $value_mode = "raw";
        if($this->hasArg('friendly')){
            $this->filesystem->addListHelper(array('WebservicesController', 'setFriendlyValues'));
        }

        $output_mode = "xml";
        if($this->hasArg('json')){
            $output_mode = "json";
        }
        $path = $this->getArg('path','');
        $limit = $this->getArg('limit','/\d*/');
        $offset = $this->getArg('offset','');

        $this->filesystem->addListHelper(array('Model_Mime', 'setMimeType'));

        $files = $this->filesystem->listDirectory($path);
        if($files){
            $files['count'] = count($files['contents']);
            $files['contents'] =  array_slice($files['contents'], $offset, $limit);
        }
        else {
            $files = array('count'=>0,'contents'=>array(),'path'=>$path);
        }
            $files['offset'] = $offset;
            $files['limit'] = $limit;



            $this->view->files = $files;

            if($output_mode == "json"){
                $this->view->output = json_encode($this->view->files);
            }
            else if($output_mode == "xml"){
                $doc = new DOMDocument();
                $doc->formatOutput = true;
                $el = $doc->createElement('results');
                $doc->appendChild($el);
                $this->arrayToXML($doc, $el, $this->view->files);
                $this->view->output =  $doc->saveXML();
            }

    }

    private function arrayToXML($doc, $parent, $data) {
        foreach ($data as $key => $value) {
            if ( ! is_string($key)) {
                $key = 'result';
            }

            if (is_array($value)) {
                $el = $doc->createElement($key);
                $parent->appendChild($el);
                $this->arrayToXML($doc, $el, $value);
            } else {
                $el = $doc->createElement($key);
                $el->appendChild($doc->createTextNode($value));
                $parent->appendChild($el);
            }
        }
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

