<?php
/* $Revision: $
 *
 * Copyright (c) 2010 Regents of the University of Michigan.
 * All rights reserved.
 */
class Model_Afs extends Filedrawers_Filesystem
{
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
        $cmd      = "$this->afsUtils/fs sa $neg " . escapeshellarg( $path ) .
        " $entity $rights";
        $cmdRecur = "find " . escapeshellarg( $path ) . " -type d -exec " .
            "$this->afsUtils/fs sa $neg {} $entity $rights \\;";
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
        $cmd = "$this->afsUtils/fs listacl " . escapeshellarg( $path );
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


    public static function setPermissions(&$row)
    {
        if ($row['filename'] == '.') {
            $row['perms'] = Model_Afs::getCallerAccess($row['filename']);
        } else {
            return;
        }
    }


    protected static function getCallerAccess($path)
    {
        $utilsPath = Zend_Registry::get('config')->afs->utilitiesPath;

        $cmd = "$utilsPath/fs getcalleraccess " . escapeshellarg($path);
        $result = shell_exec( $cmd . " 2>&1" );

        $acls = '';
        if (preg_match("/^Callers access to .* is (\w{1,7})$/", 
                $result, $matches )) {
            return strtolower( $matches[1] );
        }
    }
}

