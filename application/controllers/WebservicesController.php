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
        // Could later be concatenated with errorCodes from other components
        $this->view->errorCodes = $this->filesystem->errorCodes;
    }


    public function indexAction()
    {
        $this->view->path  = $this->getHomeDir();
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

        if(empty($path)){
            $path  = $this->filesystem->getHomeDir();
        }

        $this->filesystem->addListHelper(array('Model_Mime', 'setMimeType'));

        $files = $this->filesystem->listDirectory($path);
            if($files){
                $files['offset'] = $offset;
                $files['limit'] = $limit;
                $files['count'] = count($files['contents']);
                $contents_slice =  array_slice($files['contents'], $offset, $limit);
                unset($files['contents']);
                $files['contents'] = $contents_slice;
            }
            else {
                if($this->filesystem->errorCode){
                    $files['errorCode'] = $this->filesystem->errorCode;
                    $files['errorMsg'] = $this->filesystem->errorMsg;
                } else {
                    $files = array('count'=>0,'contents'=>array(),'path'=>$path);
                }
            }



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

        $path = $this->getArg('path','');
        $name = '"' . str_replace('"', '\"',
                basename($path)) . '"';

        header( 'Content-Description: File Transfer' );
        header( 'Content-Type: application/force-download' );
        header( 'Content-Length: ' . $this->filesystem->getSize($path));
        header( "Content-Disposition: attachment; filename=$name;");
        $this->filesystem->readfile($path);
    }


    public function renameAction()
    {
        //$path = Router::getInstance()->getFSpath();
        $path = $this->getArg('path','');

        $oldPath = $path . '/' . $_POST['oldName'];
        $newPath = $path . '/' . $_POST['newName'];

        $this->view->setNoRender();

        $output_mode = "xml";
        if($this->hasArg('json')){
            $output_mode = "json";
        }

        if ($this->filesystem->rename($oldPath, $newPath)) {
            $this->view->response = array('status' => 'success', 'message' => $this->filesystem->notifyMsg);
        } else {
            $this->view->response = array('status' => 'fail', 'message' => $this->filesystem->errorMsg);
        }
            if($output_mode == "json"){
                $this->view->response = json_encode($this->view->response);
            }
            else if($output_mode == "xml"){
                $doc = new DOMDocument();
                $doc->formatOutput = true;
                $el = $doc->createElement('results');
                $doc->appendChild($el);
                $this->arrayToXML($doc, $el, $this->view->response);
                $this->view->response =  $doc->saveXML();
            }
        echo $this->view->response;
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
        //$path = Router::getInstance()->getFSpath();

        $path = $this->getArg('path','');

        $output_mode = "xml";
        if($this->hasArg('json')){
            $output_mode = "json";
        }
        if ($this->filesystem->deleteFiles($path, $_POST['files'])) {
            $this->view->response = array('status' => 'success', 'message' => $this->filesystem->notifyMsg);
        } else {
            $this->view->response = array('status' => 'fail', 'message' => $this->filesystem->errorMsg);
        }

            if($output_mode == "json"){
                $this->view->response = json_encode($this->view->response);
            }
            else if($output_mode == "xml"){
                $doc = new DOMDocument();
                $doc->formatOutput = true;
                $el = $doc->createElement('results');
                $doc->appendChild($el);
                $this->arrayToXML($doc, $el, $this->view->response);
                $this->view->response =  $doc->saveXML();
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

