<?php

/*
 * Copyright (c) 2005 Regents of The University of Michigan.
 * All Rights Reserved.  See COPYRIGHT.
 */

require_once( '../../objects/makewebspace.php' );
require_once( '../../smarty/smarty.custom.php' );

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

    RenderMakewebspaceDiv($_SERVER['REMOTE_USER'], $Spaces);
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

?>
