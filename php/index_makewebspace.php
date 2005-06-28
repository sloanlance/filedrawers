
<?php
echo '<h2>Make-webspace</h2>';

$fs_bin = '/usr/bin/fs';
if ( !( file_exists( $fs_bin ))) {
    echo "Couldn't locate fs utility!\n";
    exit( 1 );
}

$uniqname = $_SERVER['REMOTE_USER'];
$help = 'http://www.umich.edu/how-to-homepage.html';
$Spaces = array( );
$Uses = array( 'Public', 'Private' );
$URIs = array(
    $uniqname.'_Public' => 'http://www-personal.umich.edu/~',
    $uniqname.'_Private' => 'https://personal.www.umich.edu/~',
    'group_Public' => 'http://www.umich.edu/~',
    'group_Private' => 'https://cgi.www.umich.edu/~'
);
$Ready = array( );
?>

<p>This utility will help you through both directory creation and setting
permissions in order to set up your webspace. We have scanned the various
<a href="http://www.itd.umich.edu/itcsdocs/s4033/">pts groups</a>
to determine which webspaces you may have administrative privileges
over. If you feel that you're missing an entry, please contact the <a
href="http://www.itd.umich.edu/accounts/">ITCS Accounts Office</a>.</p>

<?php

#--- if not a friend account, then get groups/uniqname and paths
if ( $_SERVER['REMOTE_REALM'] != 'friend' ) {
    $Spaces = WhichGroups( $uniqname );
}

#--- actually do the requested work
if ( isset( $_POST['make-ws-posted'] )) {
    unset( $_POST['make-ws-posted'] );
    
    #--- begin make ---
    $dir = '';
    $uniqname = $_SERVER['REMOTE_USER'];
    $help = 'http://www.umich.edu/how-to-homepage.html';
    $example = '/afs/umich.edu/group/itd/umweb/Public/html/example/index.html';
    $Matches = array( );
    
    if ( !count( $_POST )) {
        echo '<div id="notice">'.
            '<h3>No directories were chosen to set up.</h3></div>';
    } else {
        echo '<div id="notice">'."\n".'<h3>Prepared webspaces:</h3>';
    
        foreach( $_POST as $name=>$on )
        {
            preg_match( '/^(\w{1,64})_(Public|Private)$/', $name, $Matches );
            if ( !isset( $Matches[1] )) {
                continue;
            }
            $homedir = $Matches[1];
            $dir = $Matches[2];
    
            # if they're trying to inject a name they shouldn't
            if ( !isset( $Spaces[$homedir] )) {
                PassTheBuck( );
                exit( 0 );
            }
    
            if ( !( $homepath = GetDir( $homedir ))) {
                echo "<p>Couldn't get path for $homedir</p>";
                exit( 1 );
            }
            if ( !SetPerms( $homepath, 0, 'l', $fs_bin )) {
                echo "<p>Couldn't set permissions on $homepath</p>";
                exit( 1 );
            }
            if ( !CreateDir( "$homepath/$dir", 0, $fs_bin )) {
                echo "<p>Couldn't create $homepath/$dir directory</p>";
                exit( 1 );
            }
            if ( !CreateDir( "$homepath/$dir/html", 1, $fs_bin )) {
                echo "<p>Couldn't create $homepath/$dir/html directory</p>";
                exit( 1 );
            }
    
            if ( !file_exists( "$homepath/$dir/html/index.html" ) &&
                !file_exists( "$homepath/$dir/html/index.htm" ) &&
                !file_exists( "$homepath/$dir/html/index.shtml" ) &&
                !file_exists( "$homepath/$dir/html/default.html" )) {
    
                if ( !copy( $example, "$homepath/$dir/html/index.html" )) {
                    echo "<p>Wasn't able to copy default example</p>";
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
    #--- finish make ---
}

#---- finish html for form above ---
if ( count( $Spaces )) {
    $reason = '';
    ksort( $Spaces );
?>

<form action="<?= $_SERVER['PHP_SELF']; ?>" method="post">
<input type="hidden" name="make-ws-posted" value="1">

<?php
    $entry = '';
    $fix_any = 0;
    foreach( $Uses as $use ) {
        $being_used = 0;
        
        foreach( $Spaces as $name=>$path ) {
            if ( !is_dir( $path )) {
                echo "<p>$name - error: home directory doesn't exist.\n";
                PassTheBuck( );
                continue;
            }

            $directory = "$path/$use/html";
            $setup = 0;
            if ( file_exists( $directory )) {

                $count = 0;
                $Check = `$fs_bin la $path`;
                if ( preg_match( '/umweb:servers r?l/', $Check )) {
                    $count++;
                }

                $Check = `$fs_bin la $path/$use`;
                if ( preg_match( '/umweb:servers r?l/', $Check )) {
                    $count++;
                }

                $Check = `$fs_bin la $path/$use/html`;
                if ( preg_match( '/umweb:servers rl/', $Check )) {
                    $count++;
                }

                if ( $count == 3 ) {
                    $setup = 1;
                    if ( preg_match( '/group/', $directory )) {
                        array_push( $Ready, 
                            "$use $name ".
                            '<a href="'.$URIs['group_'.$use].$name.'/">'.
                            $URIs['group_'.$use].$name."/</a>" );
                    }
                    else {
                        array_push( $Ready, 
                            "$use $name ".
                            '<a href="'.$URIs[$name.'_'.$use].$name.'/">'.
                            $URIs[$name.'_'.$use].$name."/</a>" );
                    }
                } elseif ( preg_match( '/irapweb:cgi-servers rl/', $Check ) ||
                    preg_match( '/itdwww rl/', $Check )) {
                    $reason = "update permissions";
                } else {
                    $reason = "set permissions";
                }

            } else {
                $reason = "create directory and set permissions";
            }

            if ( !$setup ) {
                if ( !$being_used ) {
                    $entry .= "<h3>$use:</h3>";
                    $being_used = 1;
                }
                $fix_any = 1;
                $entry .= '<input type="checkbox" name="'.$name.'_'.$use.'"> '.
                    "$name - $reason<br />\n";
            }
        }
    }

    if ( $fix_any ) {
?>

        <p>Please select all that you would like to set up as webspace.</p>

        <blockquote>
            <?= $entry; ?>
            <br /><input type="submit" value="set up &rarr;">
        </blockquote>
        </form>

        <?php
    } else {
        echo '<span id="notice">It appears that you have setup all of '.
            'your available web spaces.</span>';
    }

    if ( count( $Ready )) {
        echo '<h3>The following web spaces are already set up:</h3>'.
            "\n<ul>\n";
        foreach( $Ready as $uri ) {
            echo "<li>$uri</li>\n";
        }
        echo "</ul>\n".
            '<p>Further assistance is available on how to <a
            href="http://www.umich.edu/~umweb/how-to/homepage.html">Create
            your own UM web page</a>.</p>';
    }

}
else {
    echo "<p>You do not have permission to create a webspace, 
        <b>$uniqname</b>.</p>\n";
    PassTheBuck( );
    echo '<p>You may also <a href="/cgi-bin/logout">logout</a>.</p>'."\n";
}

#---- library functions ----

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
function CreateDir( $directory, $recursive, $fs_bin )
{
    if ( !is_dir( $directory )) {
        if ( !mkdir( $directory )) {
          echo "<p>Couldn't create directory: $directory</p>\n";
          exit( 1 );
        }
        #echo "<p>Directory created: $directory</p>";
    }
    $set = SetPerms( $directory, $recursive, 'rl', $fs_bin );

    return $set;
}
    
#--- function for setting permissions ---
function SetPerms( $directory, $recursive, $perms, $fs_bin )
{
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

?>
