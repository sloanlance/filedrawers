<?php
/* $Revision: $
 *
 * Copyright (c) 2010 Regents of the University of Michigan.
 * All rights reserved.
 */
class Filedrawers_Filesystem_Mounted_Afs extends Filedrawers_Filesystem_Mounted
{
    private $fsUtil = 'fs';

    public function init()
    {
        if ( ! $rc = parent::init()) {
            return $rc;
        }

        //$this->addListHelper(array($this, 'setPermissions'));
        return TRUE;
    }

    public function getPermissions($path)
    {
        $permissions = parent::getPermissions();
        $rights = array( 'l', 'r', 'w', 'i', 'd', 'k', 'a' );
        $access = $this->getCallerAccess($path);
        if (is_dir($path)) {
            $map = array(
                'l' => 'read',
                'i' => 'write',
                'd' => 'delete',
                'k' => 'lock',
                'a' => 'admin'
            );
        } else {
            $map = array(
                'r' => 'read',
                'w' => 'write',
                'd' => 'delete',
                'k' => 'lock'
            );
        }

        foreach ($rights as $right) {
            if (strpos($access, $right) !== FALSE and array_key_exists($right, $map)) {
                $permissions[$map[$right]] = TRUE;
            }
        }

        return $permissions;
    }

    // Change the ACL for a given path
    public function changeAcl($entity,
                       $rights,
                       $path='',
                       $recursive=false,
                       $negative=false )
    {
        $entity   = escapeshellarg( $entity );
        $rights   = escapeshellarg( trim( $rights ));
        $path    = ( $path ) ? $path : $this->path;
        $neg      = ( $negative ) ? ' -negative' : '';
        $cmd      = "$this->fsUtil sa $neg " . escapeshellarg( $path ) .
        " $entity $rights";
        $cmdRecur = "find " . escapeshellarg( $path ) . " -type d -exec " .
            "$this->fsUtil sa $neg {} $entity $rights \\;";
        $cmd      = ( $recursive ) ? $cmdRecur : $cmd;

        if ( !$path ) {
            return false;
        }

        if ( strpos( shell_exec( $cmd . " 2>&1" ), 'fs:' ) !== false ) {
            $this->errorMsg =
                "Warning: Unable to modify the access control list.";
            return false;
        }

        return true;
    }

    // Return an array of ACL rights for the current path
    public function readAcl($path)
    {
        $cmd = "$this->fsUtil listacl " . escapeshellarg( $path );
        $result = shell_exec( $cmd . " 2>&1" );
        $rights = array( 'l', 'r', 'w', 'i', 'd', 'k', 'a' );

        if ( ! $path ) {
            return false;
        }

    if ( preg_match( '/^fs:/', $result )) {
        $this->errorMsg =
            "Warning: Unable to read the access control list.";
            return false;
        }

        $result   = preg_replace( "/(.*)is\n(.*)rights:\n/", "", $result );
        $result   = explode( "\nNegative rights:\n", $result );

        if ( isset( $result[0] )) {
            $normal = explode( "\n", trim( $result[0] ));
            if ( is_array( $normal )) {
                foreach ( $normal as $item ) {
                    $perm = explode( ' ', trim( $item ));
                    $setRights = $perm[1];
                    foreach ( $rights as $right ) {
                        if ( strpos( $setRights, $right ) !== false ) {
                            $result['normal'][$perm[0]][$right] = true;
                        } else {
                            $result['normal'][$perm[0]][$right] = false;
                        }
                    }
                }
            }
        }

        if ( isset( $result[1] )) {
            $negative = explode( "\n", trim( $result[1] ));
            if ( is_array( $negative )) {
                foreach ( $negative as $item ) {
                    $perm = explode( ' ', trim( $item ));
                    $setRights = $perm[1];
                    foreach ( $rights as $right ) {
                        if ( strpos( $setRights, $right ) !== false ) {
                            $result['negative'][$perm[0]][$right] = true;
                        } else {
                            $result['negative'][$perm[0]][$right] = false;
                        }
                    }
                }
            }
        }

        return $result;
    }


    public function setPermissions(&$row)
    {
        if ($row['filename'] == '.') {
            //$row['perms'] = Filedrawers_Filesystem_Mounted_Afs::getCallerAccess($row['filename']);
            $row['perms'] = $this->getPermissions($row['filename']);
        } else {
            $row['perms'] = $this->getPermissions($row['filename']);
            return;
        }
    }


    protected static function getCallerAccess($path)
    {

        $cmd = "fs getcalleraccess " . escapeshellarg($path);
        $result = shell_exec( $cmd . " 2>&1" );

        $acls = '';
        if (preg_match("/^Callers access to .* is (\w{1,7})$/", 
                $result, $matches )) {
            return strtolower( $matches[1] );
        }
    }

    public function getQuota($path)
    {
        $utilsPath = Zend_Registry::get('config')->afs->utilitiesPath;

        $cmd = "$utilsPath/fs listquota " . escapeshellarg($path);
        $result = shell_exec( $cmd . " 2>&1" );
        $resultLines = explode("\n", $result);
        $quotaParts = preg_split('/ +/', trim($resultLines[1]));

        return array('total' => $quotaParts[1], 'used' => $quotaParts[2]);
    }
}

