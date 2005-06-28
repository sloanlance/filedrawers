
<?php

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

?>
</div>

