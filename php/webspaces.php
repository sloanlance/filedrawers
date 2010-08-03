<?php

/*
 * Copyright (c) 2005 Regents of The University of Michigan.
 * All Rights Reserved.  See COPYRIGHT.
 */  

class Webspaces {

    public $uniqname = null;
    private $spaces = null;

    private $obsolete_groups = array("irapweb:cgi-servers", "itdwww");

    const EXAMPLE_PAGE =
           '/afs/umich.edu/group/itd/umweb/Public/html/example/index.html';

    const URL_PERS_PUBLIC = 'http://www-personal.umich.edu/~';
    const URL_PERS_PRIVATE = 'https://personal.www.umich.edu/~';
    const URL_GROUP_PUBLIC = 'http://www.umich.edu/~';
    const URL_GROUP_PRIVATE = 'https://private.www.umich.edu/~';

    const WEBSPACE_STATUS_PREPARED = 0;
    const WEBSPACE_STATUS_NODIR = 1;
    const WEBSPACE_STATUS_BAD_PERMS = 2;
    const WEBSPACE_STATUS_NOHOMEDIR = 4;

    const WEBSPACE_PRIVATE_SUF = "Public/html";
    const WEBSPACE_PUBLIC_SUF = "Private/html";

    const WEBSPACE_PTS = "umweb:servers";

    const FS_BINARY = "/usr/bin/fs";

    const VISIBILITY_ALL = 0;
    const VISIBILITY_PUBLIC = 1;
    const VISIBILITY_PRIVATE = 2;

    const STATUS_ALL = 0;
    const STATUS_PREPARED = 1;
    const STATUS_UNPREPARED = 2;

    /*
     * Creates a new set of webspaces, associated with the given
     * user.
     */
    function __construct()
    {
	$error_msg = null;
	$dir = null;

	if (!extension_loaded('posix')) {
	    if (!dl('posix.so')) {
		throw new Exception("Couldn't load necessary posix function");
	    }
	}

        $this->uniqname = $_SERVER['REMOTE_USER'];
	$this->spaces = array();

	$pts_command = '/usr/bin/pts mem '. $this->uniqname . ' -noauth';
	$pts_output = shell_exec( $pts_command );
	$Pts_groups = explode( "\n", $pts_output );

	// Create a list of potential webspaces for this user.
	GetHomeDir( $this->uniqname, $dir, $error_msg );

    // Add Private and Public webspaces
	$this->init_webspace( $this->uniqname, $dir );

	/*
	 * Check each PTS group to see if it has an associated
	 * AFS directory space. If it does, add both Public and
	 * private versions to the list of of possible webspaces.
	 */
	foreach ( $Pts_groups as $g ) {
		$g = trim( $g );
		if ( !preg_match( "/:/", $g )) {
			if ( GetHomeDir( $g, $dir, $error_msg )) {
				$this->init_webspace( $g, $dir );
			}
		}
	}

	ksort( $this->spaces );

        foreach( $this->spaces as $name=>$junk ) {
	    $this->check_webspace($name);
	}

    }

    /*
     * Return the array of webspaces.
     * The returned array can be resricted to display
     * by visibilty and by prepared status, with two
     * optional arguments.
     */
    public function get($visibility=self::VISIBILITY_ALL,
                        $prepared=self::STATUS_ALL)
    {
	$spaces = $this->spaces;

	switch($visibility) {
	case self::VISIBILITY_PUBLIC:
	    $spaces = array_filter($spaces, array($this, "is_public"));
	    break;
	case self::VISIBILITY_PRIVATE:
	    $spaces = array_filter($spaces, array($this, "is_private"));
	    break;
	default:
	case self::VISIBILITY_ALL:
	    break;
	}

	switch($prepared) {
	case self::STATUS_PREPARED:
	    $spaces = array_filter($spaces, array($this, "is_prepared"));
	    break;
	case self::STATUS_UNPREPARED:
	    $spaces = array_filter($spaces, array($this, "is_unprepared"));
	    break;
	default:
	case self::VISIBILITY_ALL:
	    break;
	}

	return $spaces;
    }

    private function is_public($space)
    {
	return $space["public"];
    }

    private function is_private($space)
    {
	return ! ($space["public"]);
    }

    private function is_prepared($space)
    {
	return $space["status"] == self::WEBSPACE_STATUS_PREPARED;
    }

    private function is_unprepared($space)
    {
	return ($space["status"] == self::WEBSPACE_STATUS_PREPARED ) ?
		0 : 1;
    }

    /*
     * Check to see the status of a named webspace,
     * and set the status code to one of the
     * WEBSPACE_STATUS_* constants
     */
    private function check_webspace($name)
    {

	if (! array_key_exists($name, $this->spaces)) {
	    return;
	}

	$webspace = &$this->spaces[$name];

	if (! is_dir($webspace["homedir"])) {
	    $webspace["status"] = self::WEBSPACE_STATUS_NOHOMEDIR;
	    $webspace["status_readable"] =
		    $this->get_readable_status($webspace["status"]);
	    return;
	}

	if (! (file_exists($webspace["path"]) && is_dir($webspace["path"]))) {
	    $webspace["status"] = self::WEBSPACE_STATUS_NODIR;
	    $webspace["status_readable"] =
		    $this->get_readable_status($webspace["status"]);
	    return;
	}

	/*
	 * We use one flag for homedir, homedir/{Public|Private}
	 * and the html directory. Setting this flag means that
	 * we'll attempt to set all three permissions.
	 */


        // Check permissions for homedir
	$Check = shell_exec(self::FS_BINARY . " la " . $webspace["homedir"]);
	if ( ! preg_match( '/umweb:servers r?l/', $Check )) {
	    $webspace["status"] = self::WEBSPACE_STATUS_BAD_PERMS;
	    $webspace["status_readable"] =
		    $this->get_readable_status($webspace["status"]);
	    return;
	}

        // Check permissions for Public/Private directory

	$int_dir = $webspace["homedir"] . "/" .
	           (($webspace["public"]) ? "Public" : "Private");

	$Check = shell_exec(self::FS_BINARY . " la " . $int_dir);
	if ( ! preg_match( '/umweb:servers r?l/', $Check )) {
	    $webspace["status"] = self::WEBSPACE_STATUS_BAD_PERMS;
	    $webspace["status_readable"] =
		    $this->get_readable_status($webspace["status"]);
	    return;
	}

        // Check permissions for web directory
	$Check = shell_exec( self::FS_BINARY . " la " . $webspace["path"]);
	if ( ! preg_match( '/umweb:servers rl/', $Check )) {
	    $webspace["status"] = self::WEBSPACE_STATUS_BAD_PERMS;
	    $webspace["status_readable"] =
		    $this->get_readable_status($webspace["status"]);
	    return;
	}

       /* 
        * Out-of-date permissions that need to be removed.
	*/
	$Check = shell_exec(self::FS_BINARY . " la " . $webspace["path"]);
	if ( preg_match( '/irapweb:cgi-servers rl/', $Check ) ||
	    preg_match( '/itdwww rl/', $Check )) {
	    $webspace["status"] = self::WEBSPACE_STATUS_BAD_PERMS;
	    $webspace["status_readable"] =
		    $this->get_readable_status($webspace["status"]);
	    return;
	}

	$webspace["status"] = self::WEBSPACE_STATUS_PREPARED;
	$webspace["status_readable"] =
		$this->get_readable_status($webspace["status"]);

	return;
    }

    /*
     * Perform preparation on all site names found in
     * the POST variable.
     *
     * Returns an array of "site" / "result" / "success" triplets.
     */
    public function prepare()
    {
	$ret_array = array();

	foreach( $_POST as $name=>$junk ) {
	    $error = "";
	    $rc = $this->prepare_space($name, $error);
	    array_push($ret_array,
	               array(
			     "site" => $name,
		             "result" => (($rc) ? "sucessfully prepared."
		                         : $error),
			     "success" => $rc
			   )
		       );
	}

        // Verify that we've prepared sites and set status.
        foreach( $this->spaces as $name=>$junk ) {
	    $this->check_webspace($name);
	}

	return (empty($ret_array) ? null : $ret_array); 
    }

    /*
     * Perform preparation on the requested website.
     * 
     * Returns "1" on succesful creation.
     *
     * Returns "0" on failure and sets "error"
     * with a human-readable error message.
     *
     */
    private function prepare_space($name, &$error)
    {

	# if they're trying to inject a name they shouldn't
	if (! array_key_exists($name, $this->spaces)) {
	    $error = "$name is not a valid website to prepare.";
	    return 0;
	}

	$webspace = &$this->spaces[$name];

        // Set web permissions on user's home directory.
	if ( ! $this->SetPerms( $webspace["homedir"],
		       self::WEBSPACE_PTS,
	               0,
	               'l',
	               $error_msg )) {
	    $error = "couldn't set permissions on " . $webspace["homedir"] .
	             ".";
	    return 0;
	}

        // Create intermediate Public/Private directory

        $int_dir = $webspace["homedir"] . "/" .
                   (($webspace["public"]) ? "Public" : "Private");

	if ( ! $this->CreateDir( $int_dir , $error_msg)) {
	    $error = "couldn't create directory " . $int_dir . ".";
	    return 0;
	}

        // Set permissions for Public/Private directory
	if ( ! $this->SetPerms( $int_dir,
		       self::WEBSPACE_PTS,
	               0, 'lr', $error_msg )) {
	    $error = "couldn't set permissions on " . $int_dir . ".";
	    return 0;
	}

        // Create the web directory.
	if ( ! $this->CreateDir( $webspace["path"], $error_msg )) {
	    $error = "couldn't create " . $webspace["path"] . " directory.";
	    return 0;
	}

        // Set web permissions (recursively) on the web directory.
	if ( ! $this->SetPerms($webspace["path"],
		      self::WEBSPACE_PTS,
	              1, 'lr', $error_msg )) {
	    $error = "couldn't set permissions on directory " .
		     $webspace["path"] . ".";
	    return 0;
	}

       /* 
        * Remove out-of-date permissions.
	*/

	foreach ( $this->obsolete_groups as $obsolete_group ) {

	    if ( ! $this->SetPerms($webspace["path"],
			  $obsolete_group,
			  1, 'none', $error_msg )) {
		$error = "couldn't set permissions on directory " .
			 $webspace["path"] . ".";
		return 0;
	    }
	}

	if ( !file_exists( $webspace["path"] . "/index.html" ) &&
	    !file_exists( $webspace["path"] . "/index.htm" ) &&
	    !file_exists( $webspace["path"] . "/index.shtml" ) &&
	    !file_exists( $webspace["path"] . "/default.html" )) {

	    if ( !copy(self::EXAMPLE_PAGE,
                       $webspace["path"] . "/index.html" )) {
		$error = "couldn't copy default example.";
		return 0;
	    }
	}

        $error = "";
	return 1;

    }

    // Returns the appropriate url based on its visibility, sitename and path
    private function get_website_url($is_private, $sitename, $homepath )
    {
	
	$is_group = preg_match( '/group/', $homepath );

	if ($is_group) {
	    if ($is_private) {
		$uri = self::URL_GROUP_PRIVATE;
	    } else {
		$uri = self::URL_GROUP_PUBLIC;
	    }
	} else {
	    if ($is_private) {
                $uri = self::URL_PERS_PRIVATE; 
	    } else {
                $uri = self::URL_PERS_PUBLIC; 
	    }
	}

	return "$uri$sitename/index.html";
    }

    // Initialize Public and Private webspaces
    private function init_webspace($sitename, $dir)
    {
	$this->spaces[$sitename . "_private"] =
	       array(
			"name" => $sitename . " Private",
			"path" => $dir . "/Private/html",
			"homedir" => $dir,
			"public" => 0,
			"url" => $this->get_website_url(1, $sitename, $dir),
			"status" => self::WEBSPACE_STATUS_PREPARED,
			"status_readable" => ""
		    );

	$this->spaces[$sitename . "_public"] =
	       array(
			"name" => $sitename . " Public",
			"path" => $dir . "/Public/html",
			"homedir" => $dir,
			"public" => 1,
			"url" => $this->get_website_url(0, $sitename, $dir),
			"status" => self::WEBSPACE_STATUS_PREPARED,
			"status_readable" => ""
		    );
    }

    // Sets permissions on a directory.
    //
    // Setting "recursive" to true sets the permissions on the directory
    // recursively.
    //
    // Returns 1 on success.
    //
    // Returns 0 on failure and sets "error_msg" with a human-readable error.
    //
    private function SetPerms($directory, $group, $recursive,
                              $perms, &$error_msg )
    {

	if ( $recursive ) {
	    $command = "find $directory -type d ".
		"-exec " . self::FS_BINARY . " sa -dir {} -acl $group" .
		" $perms \\;";
	} else {
	    $command = self::FS_BINARY . " sa -dir $directory -acl $group" .
	               " $perms";
	}

	$perm_set = shell_exec($command);

	if ( strlen( $perm_set )) {
	    $error_msg .= "Could not set privileges: $directory.";
	    return 0;
	}

	return 1;
    }

    // Creates a directory.
    //
    // Returns 1 on success.
    //
    // Returns 0 on failure and sets "error_msg" with a human-readable error.
    // If the directory already exists, this returns successfully.
    //
    private function CreateDir( $directory, &$error_msg )
    {
	if ( !is_dir( $directory )) {
	    if ( !mkdir( $directory )) {
	      $error_msg .= "Couldn't create directory: $directory.";
	      return 0;
	    }
	}

	return 1;
    }

    public function get_readable_status($status)
    {
	switch($status) {
	case self::WEBSPACE_STATUS_PREPARED:
	    return "Website has been properly prepared.";
	    break;
	case self::WEBSPACE_STATUS_NODIR:
	    return "Website hasn't been created.";
	    break;
	case self::WEBSPACE_STATUS_BAD_PERMS:
	    return "Website has incorrect permissions.";
	    break;
	case self::WEBSPACE_STATUS_NOHOMEDIR:
	    return "Website user has no home directory.";
	    break;
	default:
	    return "Unknown status.";
	    break;
	}
    }

}

    
?>
