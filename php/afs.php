<?php
/*
 * Copyright (c) 2005 Regents of The University of Michigan.
 * All Rights Reserved.  See COPYRIGHT.
 */

require_once( 'mime.php' );
define( "CLIPSEPARATOR", "*#~!@@@" );

if ( !extension_loaded( 'posix' )) {
    if ( !dl( 'posix.so' )) {
        error_log( "Couldn't load necessary posix function" );
        echo "<p>Couldn't load necessary posix function</p>\n";
        exit( 1 );
    }
}

// remember this class needs to have $this->path set
class Afs
{
    protected $selectedItems;
    protected $afsUtils      = '/usr/bin';
    protected $folderLocs    = array( 'site' => 'public/html/' );
    public    $confirmMsg    = '';
    public    $override      = 0;
    public    $errorMsg      = '';
    public    $notifyMsg     = '';
    public    $parPath;           // Path to the parent of current path
	public    $filename      = '';
    #this stuff doesn't quite work yet, so set it to 1 for now
    public    $writable      = 0; // Can we write to this directory?
    public    $readable      = 1; // Can we read from this directory?
    public    $viewable      = 1; // Can we view the contents of this directory?
    public    $path          = '';
    public    $sid;
	public    $type          = '';
	public    $mimetype      = '';
    private   $uniqname      = '';
    protected $newName       = '';

    public function __construct( $path="" )
    {
        $this->sid = md5( uniqid( rand(), true ));
		$this->uniqname = $_SERVER['REMOTE_USER'];
        $this->setPath( $path );
		$this->type = $this->getType( );

		// Generate the path of the folder one level above the current
		preg_match( "/(.*\/)([^\/]+)\/?$/", $this->path, $Matches );
		$this->parPath = $Matches[1];
		$this->filename = $Matches[2];

		/*
		if ( is_dir( $this->path )) {
            $this->viewable= 1;
        }
        if ( is_readable( $this->path )) {
            $this->readable= 1;
        }
		*/
        if ( posix_access( $this->path, POSIX_W_OK )) {
            $this->writable = 1;
        }

        $this->processCommand();
    }

	public function getType( )
	{
		if ( !file_exists( $this->path )) {
			return 'none';
		}

		if ( is_dir( $this->path )) {
			$type = 'dir';
		} elseif ( is_file( $this->path )) {
			$type = 'file';
			$this->mimetype = Mime::getMimeType( $this->path );
		}
		return $type;
	}

    public function processCommand()
    {
        if ( ! isset( $_POST['command'] )) {
            return false;
        }

        $this->setSelectedItems();

        switch ( $_POST['command'] ) {
            case 'newfolder':
                $this->createFolder();
                break;
            case 'rename':
                $this->setNewItemName();
                $this->afsRename();
                break;
            case 'cut':
                $this->setOriginPath();
                $this->moveFiles();
                break;
            case 'copy':
                $this->setOriginPath();
                $this->copyFiles();
                break;
            case 'delete':
                $this->deleteFiles();
                break;
            default:
                break;
        }
    }


    /*
     * This function sets the "target" of an operation
     * (what file(s) or folder(s)
     * to perform the selected action on.
     */
    protected function setSelectedItems()
    {
        if ( isset( $_POST['selectedItems'] )
          && is_array( $_POST['selectedItems'] )) {
            $this->selectedItems = array();

            foreach ( $_POST['selectedItems'] as $key=>$item ) {
                $this->selectedItems[$key] = $item;
            }
        } else if ( isset( $_POST['selectedItems'] )) {
            $this->selectedItems = $_POST['selectedItems'];
        }
    }


    // Some functions like cut or paste need to know where a file is coming from
    // in addition to where it is going to
    public function setOriginPath()
    {
        if ( isset( $_POST['originPath'] )) {
            $this->originPath = $this->pathSecurity( $_POST['originPath'] );
        }
    }


    public function setNewItemName()
    {
        if ( isset( $_POST['newName'] )) {
            $this->newName = $_POST['newName'];
        }
    }


    function createFolder()
    {
        if ( $this->selectedItems !=
		'Please enter a name for your new folder.' ) {
            if ( file_exists( $this->path . '/' . $this->selectedItems )) {
                $this->errorMsg =
			"The folder \'$this->selectedItems\' already exists." .
                        " Please select a different name.";
                  return false;
            }

            if ( ! mkdir($this->path . '/' .
			$this->selectedItems, 0644, true )) {
                $this->errorMsg = "Unable to create folder.";
                return false;
            }

            return true;
        }
    }


    // Remove an existing folder
    // jackylee at eml dot cc
    function removeFolder( $name='', $path='' )
    {
        $dir = ( $path ) ? $path . '/' . $name : $this->path . '/' . $name;

        if ( !$dir = $this->pathSecurity( $dir )) {
            $this->errorMsg = "Unable to remove the folder.";
			return false;
        }
        if ( !$handle = @opendir( $dir )) {
            $this->errorMsg = "Unable to remove the folder because " .
		    "it no longer exists.";
            return false;
        }

        while ( false !== ( $item = readdir( $handle ))) {

            if ( $item == "." || $item == ".." ) {
                continue;
            }

            if ( is_dir( "$dir/$item" ) && !is_link( "$dir/$item")) {
                $this->removeFolder( '', "$dir/$item" ); // recursive
            } else {
                unlink( "$dir/$item" );
            }
        }

        closedir( $handle );

        if ( rmdir( $dir )) {
            $this->notifyMsg = "Successfully deleted file(s).";
            return true;
        }
			
        $this->errorMsg = "Unable to remove the folder.";
        return false;
    }

    // Delete specified files
    function deleteFiles()
    {
        if ( ! $this->selectedItems ) {
            return false;
        }

        $files = explode( "\n", trim( $this->selectedItems ));

        foreach ( $files as $file ) {

            if ( !$file = $this->pathSecurity( $this->path . '/'
		  . trim( $file ))) {
	      $this->errorMsg = "Unable to delete $file";
	      return false;
            }

            if ( is_dir( $file ) && !is_link( $file )) { 
                $this->removeFolder( '', $file );
            } else if ( ! unlink( $file )) {
                $this->errorMsg = "Unable to delete $file.";
		return false;
            } else {
                $this->notifyMsg = "Successfully deleted file(s).";
            }
        }
    }

    function afsRename()
    {

        if ( $this->selectedItems == $this->newName ) {
            return false;
        }

        if ( file_exists( $this->path . '/' . $this->newName )) {
            $this->errorMsg = "The file or folder '" . $this->newName .
		    "' already exists. Please select a different name.";
            return false;
        }

        if ( !@rename( $this->path . '/' . $this->selectedItems, $this->path .
		'/' . $this->newName )) {
            $this->errorMsg = "Unable to rename this file or folder.";
            return false;
        }
    }

    /*
     * Move files from one directory to another
     * This will clobber an existing file with the same name
     */
    function moveFiles()
    {
        $files = explode( CLIPSEPARATOR, $this->selectedItems );

        foreach ( $files as $file ) {

            if ( !@rename( $this->originPath . '/' . $file, $this->path . '/'
              . $file )) {
                $this->errorMsg = "Unable to move: $file.";
                return false;
            }

            $this->notifyMsg = "Pasted the contents of the clipboard.";
        }

        return true;
    }

    // Copy file from one directory to another
    function copyFiles()
    {
        $files = explode( CLIPSEPARATOR, $this->selectedItems );

        foreach ( $files as $file ) {

            if ( is_link( $this->originPath . '/' . $file )) {
                $this->errorMsg = "$file is a link to another location in AFS."
                  . " Copying links is not currently supported.";
				return false;
            }

            if ( is_dir( $this->originPath . '/' . $file )) {
                if ( !$this->copy_dirs( $this->originPath . '/' .
			$file, $this->path . '/' . $file )) {
                    $this->errorMsg = "Unable to copy $file.";
                    return false;
                }
            } else if ( !@copy( $this->originPath . '/' . $file, $this->path .
		    '/' . $file )) {
                $this->errorMsg = "Unable to copy $file.";
                return false;
            }

            $this->notifyMsg = "Pasted the contents of the clipboard.";
        }
    }

    // A helper function for copyFiles()
    // Copy an entire directory at once
    // php.net, objobj at hotmail dot com
    function copy_dirs( $wf, $wto )
    {
        if ( !file_exists( $wto )) {
            if ( !@mkdir( $wto, 0755 )) {
                return false;
            }
        }

        $arr = $this->ls_a( $wf );

        foreach ( $arr as $fn ) {
            if ( $fn ) {
                $fl = $wf . "/" . $fn;
                $flto = $wto . "/" . $fn;

                if ( is_dir( $fl )) {
                    if ( !$this->copy_dirs( $fl, $flto )) {
                        return false;
                    }
                } else if ( ! @copy( $fl, $flto ) || ! @chmod( $flto, 0666 )) {
                    return false;
                }
            }
        }

        return true;
    }

    // A helper function for copy_dirs
    // This function lists a directory
    // aven_25041980 at yahoo dot com (php.net)
    function ls_a( $wh )
    {
        $files = '';

        if ( $handle = opendir( $wh )) {
            while ( false !== ( $file = readdir( $handle ))) {
                if ( $file !== "." && $file !== ".." ) {
                    $files = ( !$files ) ? $file : $file . "\r\n" . $files;
                }
            }
            closedir( $handle );
        }

        return explode( "\r\n", $files );
    }

    // Change the ACL for a given path
    function changeAcl($entity,
                       $rights,
                       $path='',
                       $recursive=false,
                       $negative=false )
    {
        $entity   = escapeshellarg( $entity );
        $rights   = escapeshellarg( trim( $rights ));
        $path     = ( $path ) ? $path : $this->path;
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

    // Return an arry of ACL rights for the current path
    function readAcl( $path='' )
    {
        $path = ( $path ) ? $path : $this->path;
        $cmd = "fs listacl " . escapeshellarg($path);
        $result = shell_exec( $cmd . " 2>&1" );
        $rights = array( 'l', 'r', 'w', 'i', 'd', 'k', 'a' );

        if ( !$path ) {
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

    /*
     * List the contents of a folder as a set of javascript
     * variable declarations.
     *
     */
    public function get_foldercontents_js( $showHidden=false )
    {
        $id = 0;
        $files = '';

        // Open the path and read its contents
        if ( !@is_dir( $this->path ) || !$dh = @opendir( $this->path )) {
            $this->errorMsg = "Unable to view: $this->path.";
            return false;
        }

        while ( $filename = readdir( $dh )) {
            $fullpath = "$this->path/$filename";

            if ( !$size = @filesize( $fullpath )) {
                $size = 0;
            }

            $modTime = @filemtime( $fullpath );
            $mime    = Mime::mimeIcon( $fullpath );

			$filename = $this->escape_js($filename);

            if ( $showHidden ) {
                $files .= "files[$id]=new File('$filename', '$modTime', $size, "
                  . "'', '$mime');\n";
            } else if ( strpos( $filename, '.' ) !== 0 ) {
                $files .= "files[$id]=new File('$filename', '$modTime', $size, "
                  . "'', '$mime');\n";
            }

            $id++;
        }

        closedir( $dh );
        return $files;
    }

    function get_foldername()
    {
	return basename( $this->path );
    }

    function get_returnToURI()
    {
        return ( 'https://' .
                  $_SERVER['HTTP_HOST'] .
	          $_SERVER['PHP_SELF'] .
	          "?path=" .
	          urlencode($this->path) .
	          "&" .
	          "finishid=" .
                  $this->sid );
    }

    /*
     * Return a string escaped for a javascript string literal.
     */
    function escape_js( $string )
    {
	$o="";

	$l=strlen($string);
	for($i=0;$i<$l;$i++)
	{
	    $c=$string[$i];
	    switch($c)
	    {
	    case '\'':
		$o.='\\\'';
		break;
	    case '\\':
		$o.='\\\\';
		break;
	    case "\n":
		$o.='\\n';
		break;
	    case "\r":
		$o.='\\r';
		break;
	    default:
		$o.=$c;
		break;
	    }
	}

	return $o;
    }

    // Prevent users from breaking outside of afs on the web server
    function pathSecurity( $path='' )
    {
		$pos = '';
		$tail = '';
		$decrement = 0;

        if ( !$path ) {
            return false;
        }
        if ( strpos( $path, '/afs/' ) !== 0 ) {
            return false;
        }
		# if parent reference exists, trim path accordingly
        if ( strpos( $path, '..' ) !== false ) {
			$Split = explode( '/', $path );
			$path = '';

			foreach( $Split as $segment ) {
				if ( $segment == '..' ) {
					$decrement++;
				} elseif ( strlen( $segment )) {
					$path .= '/'.$segment;
				}
			}

			for ( ; $decrement > 0; $decrement-- ) {
				$path = preg_replace( '/\/[^\/]+\/?$/', '', $path );
			}
		}

        if ( is_link( $path )) {
            $target = readlink( $path );
            if ( strpos( $target, '/afs/' ) !== 0 || strpos( $target, '..' )
              !== false ) {
                return false;
            }
        }

        $pos = strrpos( $path, '/' );
        if ( $pos == strlen( $path ) - 1 ) {
            // Remove the final / in the target path if it exists
            return substr_replace( $path, '', $pos );
        }

        return $path;
    }

    // Checks to see if there is a folder at the current path
    function folderExists( $path='' )
    {
        $path = ( $path ) ? $path : $this->path;
        return is_dir( $path );
    }

    // Set the afs path used inside the class
    function setPath( $path='' )
    {
        if ( $path ) {
            if ( ! file_exists( $path )) {
				$this->errorMsg = "The specified path does not exist. ($path)";
            } else {
                $this->path = $this->pathSecurity( $path );
            }
        }

        if ( ! $this->path ) {
            $this->path = getBasePath($this->uniqname);
        }
    }

    // Makes each piece of a file path clickable
    function pathDisplay()
    {
        $path     = explode( '/', $this->path );
        $pathDisp = '';
        $pathURI  = '';
		$filename = '';
		$lastDisp = '';
		$lastURI  = '';

		while ( !strlen( $path[0] )) {
			array_shift( $path );
		}
		if ( $path[0] == 'afs' ) {
			$pathURI = '/'.array_shift( $path );
			$pathDisp = htmlentities( $pathURI );
		}

		if ( $this->getType( ) == 'file' ) {
			$filename = array_pop( $path );
		} else {
			$lastURI = '/'.array_pop( $path );
			$lastDisp = htmlentities( $lastURI );
		}

        foreach ( $path as $piece ) {
            if ( $piece == '' ) {
                continue;
            }
            $pathURI .= "/$piece";
			$pathDisp .= "/<a href=\"/?path="
			  . rawurlencode( $pathURI ) . "\">"
			  . htmlentities( $piece ) . "</a>";
        }
		$pathURI .= $lastURI;
		$pathDisp .= $lastDisp;

        return $pathDisp.'/'.$filename;
    }

    // Make smarty template variable assignments
    function make_smarty_assignments(&$smart)
    {
		$smart->assign( 'path_url', urlencode($this->path));
		$smart->assign( 'parentPath', urlencode($this->parPath ));
		$smart->assign( 'location', $this->pathDisplay());
    }

    function get_js_declarations()
    {
        $retstr = "";

        $retstr .= $this->js_var("path", $this->path);
        $retstr .= $this->js_var("foldername", $this->get_foldername());
        $retstr .= $this->js_var("folderIcon", "");
        $retstr .= $this->js_var("homepath", getBasePath($this->uniqname));
        $retstr .= $this->js_var("sid", $this->sid);
        $retstr .= $this->js_var("returnToURI", $this->get_returnToURI());
        $retstr .= $this->js_var("writable", $this->writable);
        $retstr .= $this->js_var("readable", $this->readable);
        $retstr .= $this->js_var("viewable", $this->viewable);

		if ( $this->type == 'dir' ) {
			$retstr .= "files = new Array();\n";
			$retstr .= $this->get_foldercontents_js(true);
		}

		return $retstr;
    }

    private function js_var($varname, $contents)
    {
        $retstr = "";
        $retstr .= "var $varname = " .
                   (is_string($contents) ? "'".$this->escape_js($contents)."'"
                                         : $contents) .
                   ";\n";
        return $retstr;
    }

}
?>
