<?php
/* $Revision: $
 *
 * Copyright (c) 2010 Regents of the University of Michigan.
 * All rights reserved.
 */

class Model_UploadStatus extends Zend_Db_Table
{
    protected $_name = 'filedrawers_progress';
    protected $_id = null;
    protected $errorMsg = '';


    public function __construct($id)
    {
        $this->_id = $id;
        parent::__construct();
    }


    public function isValid()
    {
        $isValid = true;

        $select = $this->select()
            ->from('filedrawers_progress', array('filename'))
            ->where("session_id = ?", $this->_id)
            ->limit(1);

        $row = $this->fetchRow($select);

        if ( ! $row) {
            $this->errorMsg = "There are no uploads in progress for the specified ID";
            return false;
        }

        // Check for upload errors
        $filename = trim($row->filename);

        if (strpos($filename, 'ERROR:') === 0 ) {
            $msgParts = explode( ':', $filename );

            if (strpos($filename, 'File exists')) {
                $this->errorMsg = "The file '" . trim( $msgParts[1] ) .
                    "' already exists. " . "The upload cannot continue.";
                $isValid = false;
            } else {
                $this->errorMsg = "One or more files did not upload sucessfully";
                $isValid = false;
            }
        }

        $select = $this->getAdapter()->quoteInto("session_id = ? OR (datediff(NOW(), last_update)) > 5", $this->_id);
        $this->delete($select);

        return $isValid;
    }


    public function getProgress()
    {
        $response = array('filename' => null, 'size' => null, 'received' => null);

        $select = $this->select()
            ->from('filedrawers_progress', array('filename', 'size', 'received'))
            ->where("session_id = ?", $this->_id)
            ->limit(1);

        $row = $this->fetchRow($select);

        if ( ! $row) {
            return $response;
        }

        return array(
            'filename' => $row->filename,
            'size' => $row->size,
            'received' => $row->received
        );
    }


    public function getMessages()
    {
        return $this->errorMsg;
    }
}

