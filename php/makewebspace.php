<?php
$fs_bin = '/usr/bin/fs';

// what "top level" pts groups does the user belong to?
function WhichGroups( $uniqname )
{
    $dir = '';

    if (!extension_loaded('posix')) {
        if (!dl('posix.so')) {
            echo "<p>Couldn't load necessary posix function</p>\n";
            exit( 1 );
        }
    }

    $pts_command = '/usr/bin/pts mem '.$uniqname.' -noauth';
    $pts_output = shell_exec( $pts_command );
    $Pts_groups = explode( "\n", $pts_output );

    if ( $dir = GetDir( $uniqname )) {
        $Clean = array( $uniqname=>$dir );
    }
    
    foreach ( $Pts_groups as $g ) {
        $g = trim( $g );
        if ( !preg_match( "/:/", $g )) {
            if ( $dir = GetDir( $g )) {
                $Clean[$g] = $dir;
            }
        }
    }
    ksort( $Clean );

    return( $Clean );
}

function GetDir( $name )
{
    if (!extension_loaded('posix')) {
        if (!dl('posix.so')) {
            echo "<p>Couldn't load necessary posix function</p>\n";
            exit( 1 );
        }
    }

    $Pwent = posix_getpwnam( $name );
    if ( !is_dir( $Pwent['dir'] )) {
        return 0;
    }
    return $Pwent['dir'];
}

function PassTheBuck ( )
{
    echo '<p>Please contact the ITCS accounts office at: '.
        '<a href="http://www.itcs.umich.edu/accounts/">'.
        'http://www.itcs.umich.edu/accounts/</a></p>'."\n";

    return 0;
}

#--- function for creating directories, also calls SetPerms ---
function CreateDir( $directory, $recursive )
{
    if ( !is_dir( $directory )) {
        if ( !mkdir( $directory )) {
          echo "<p>Couldn't create directory: $directory</p>\n";
          exit( 1 );
        }
        #echo "<p>Directory created: $directory</p>";
    }
    $set = SetPerms( $directory, $recursive, 'rl' );

    return $set;
}
    
#--- function for setting permissions ---
function SetPerms( $directory, $recursive, $perms )
{
    global $fs_bin;

    $command = "$fs_bin sa $directory umweb:servers $perms";
    if ( $recursive ) {
        $command = "find $directory -type d ".
            "-exec $fs_bin sa {} umweb:servers $perms \\;";
    }

    $perm_set = `$command`;
    if ( strlen( $perm_set )) {
        echo "<p>Could not set privileges: $directory</p>";
        exit( 1 );
    }
    #echo "<p>Permissions set on $directory</p>\n";

    return 1;
}

function RenderMakewebspaceDiv( $uniqname, $webspaces )
{
    $dir = '';
    $help = 'http://www.umich.edu/how-to-homepage.html';
    $example = '/afs/umich.edu/group/itd/umweb/Public/html/example/index.html';
    $Matches = array( );
    $rc = 1;

    echo '<div id="notice">' . "\n";

    if ( !count( $_POST )) {
	echo '<h3>No directories were chosen to set up.</h3>';
    } else {
	echo '<h3>Prepared webspaces:</h3>';

	foreach( $_POST as $name=>$on )
	{
	    preg_match( '/^(\w{1,64})_(Public|Private)$/', $name, $Matches );
	    if ( !isset( $Matches[1] )) {
		continue;
	    }
	    $homedir = $Matches[1];
	    $dir = $Matches[2];

	    # if they're trying to inject a name they shouldn't
	    if ( !isset( $webspaces[$homedir] )) {
		PassTheBuck( );
		$rc = 1;
		break;
	    }

	    if ( !( $homepath = GetDir( $homedir ))) {
		echo "<p>Couldn't get path for $homedir</p>";
		$rc = 1;
		break;
	    }
	    if ( !SetPerms( $homepath, 0, 'l', $fs_bin )) {
		echo "<p>Couldn't set permissions on " .
		     "$homepath</p>";
		$rc = 1;
		break;
	    }
	    if ( !CreateDir( "$homepath/$dir", 0, $fs_bin )) {
		echo "<p>Couldn't create " .
		     "$homepath/$dir directory</p>";
		$rc = 1;
		break;
	    }
	    if ( !CreateDir( "$homepath/$dir/html", 1, $fs_bin )) {
		echo "<p>Couldn't create " .
		     "$homepath/$dir/html directory</p>";
		$rc = 1;
		break;
	    }

	    if ( !file_exists( "$homepath/$dir/html/index.html" ) &&
		!file_exists( "$homepath/$dir/html/index.htm" ) &&
		!file_exists( "$homepath/$dir/html/index.shtml" ) &&
		!file_exists( "$homepath/$dir/html/default.html" )) {

		if ( !copy( $example, "$homepath/$dir/html/index.html" )) {
		    echo "<p>Wasn't able to copy default " .
		         "example</p>";
		}
	    }

	    #take care of private links here:
	    if ( preg_match( '/Private/', $dir )) {
		echo "Private $homedir ";
		if ( preg_match( '/group/', $homepath )) {
		    $uri = $URIs['group_Private'];
		    echo "<a href=\"$uri$homedir/index.html\">".
			 "$uri$homedir/index.html</a><br />";
		}
		else {
		    $uri = $URIs[$homedir.'_Private'];
		    echo "<a href=\"$uri$homedir/index.html\">".
			 "$uri$homedir/index.html</a><br />";
		}
	    }        
	    else {
		echo "Public $homedir ";
		if ( preg_match( '/group/', $homepath )) {
		    $uri = $URIs['group_Public'];
		    echo "<a href=\"$uri$homedir/index.html\">".
			 "$uri$homedir/index.html</a><br />";
		}
		else {
		    $uri = $URIs[$homedir.'_Public'];
		    echo "<a href=\"$uri$homedir/index.html\">".
			 "$uri$homedir/index.html</a><br />";
		}
	    }
	}
    }

    echo '</div>';

    if ($rc) {
	exit($rc);
    }
}

?>
