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

?>
