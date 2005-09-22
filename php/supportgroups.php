<?php

/*
 * Copyright (c) 2005 Regents of The University of Michigan.
 * All Rights Reserved.  See COPYRIGHT.
 */  

class Supportgroups {

    public $uniqname = null;
    private $homedir = null;
    private $supportgroups = null;

    private $db_host = "urdu.web";
    private $db_name = "mfile";
    private $db_ro_user = "mfile";
    private $db_ro_password = "mdiTG!";

    private $db = null;
    private $conn = null;

    private $affiliations = null;
    private $authorized = null;

    const AFS_BASEDIR = '/afs/umich.edu/user/';
    const FS_BINARY = "/usr/bin/fs";
    const PTS_BINARY = "/usr/bin/pts";

    /*
     * Creates a new set of support groups.
     */
    function __construct()
    {
	$error_msg = null;
	$dir = null;

        $this->uniqname = $_SERVER['REMOTE_USER'];
        $this->homedir = $this->get_homedir();

	$this->affiliations  = new Affiliations();

        // Connect to database
        $this->db_connect_ro();

        $this->update_authorized();
	$this->update_supportgroups();
	$this->update_permitted();

	return;
    }

    private function update_authorized()
    {
        // Create an array of authorized uniqnames.
	$this->authorized = array();
	$query = "select * from authorized;";
	$result = $this->db_query($query);

	while ($row = mysql_fetch_array($result)) {
	    $this->authorized[$row["uniqname"]] = $row["uniqname"];
	}
    }

    private function update_supportgroups()
    {
	$this->supportgroups = array();

        // Create an array of all known support groups.
	$query = "select * from affiliations order by name;";
	$result = $this->db_query($query);

	while ($row = mysql_fetch_array($result)) {
            $temp_group = array();
            $temp_group["id"] = $row["id"];
            $temp_group["name"] = $row["supportgroup_name"];
            $temp_group["affiliation_name"] = $row["name"];
            $temp_group["submitter"] = $row["submitter"];
	    $temp_group["affiliated"] =
                    $this->is_affiliated($temp_group["affiliation_name"]);
	    $this->supportgroups[] = $temp_group;
	}
    }

    /*
     * Update the "permitted" flag on every support group.
     */
    private function update_permitted()
    {
	foreach($this->supportgroups as $key => $garbage) {
            $this->supportgroups[$key]["permitted"] =
		    $this->is_permitted($this->supportgroups[$key]["name"]);
	}
    }

    private function get_homedir()
    {
        if ( !$this->uniqname ) {
            return false;
        }
    
        return (self::AFS_BASEDIR . $this->uniqname[0] .
                "/" . $this->uniqname[1] . "/" . $this->uniqname);
    
    }

    private function is_affiliated($affiliation_name)
    {
	foreach( $this->affiliations->get() as $affiliation) {
	    if ($affiliation["name"] == $affiliation_name) {
		return true;
	    }
	}

        return false;
    }

    /*
     * Check to see if the given PTS group has wildkar permissions
     * over the users entire AFS home directory.
     */
    private function is_permitted($groupname)
    {
        return $this->walk_dirs($this->homedir,
                                $groupname,
				array("Supportgroups",
                                      "group_is_permitted"));
    }

    /*
     * Give the given PTS group has wildkar permissions
     * over the users entire AFS home directory.
     */
    public function give_permissions($groupname)
    {
	if (!$this->is_support_group($groupname)) {
	    return false;
	}

        if (!$this->walk_dirs($this->homedir,
                              $groupname,
			      array("Supportgroups",
                                    "permit_group"))) {
	    return false;
	}

	$this->update_permitted();
	return true;
    }

    /*
     * Remove the given PTS group's permissions
     * over the users entire AFS home directory.
     */
    public function remove_permissions($groupname)
    {
	if (!$this->is_support_group($groupname)) {
	    return false;
	}

        if (!$this->walk_dirs($this->homedir,
                                $groupname,
				array("Supportgroups",
                                      "unpermit_group"))) {
	    return false;
	}

	$this->update_permitted();
	return true;
    }

    /*
     * Walk directories recursively.
     * call "callback" on every found directory.
     *
     * Returns true when all "callback" return true.
     *
     * Returns when any "callback" returns false.
     */
    private function walk_dirs($dir, $group, $callback)
    {

	if (!is_dir($dir)) {
	    return false;
	}

        $result = call_user_func($callback, $dir, $group);

        if (!$result) {
	    return false;
        }

        // decend into any subdirectories

        /*
         * Opendir can fail if we don't have afs lookup permissions
         * on the directory. We skip over directories we can't
         * read and return true.
         */
	if (!($dir_handle = @opendir ($dir))) {
            return true;
        }

	while ($entry=readdir($dir_handle)) {
            // Skip "." and ".." dir entries.
	    if ($entry=="." or $entry=="..") {
		continue;
            }

            $entry_path = ($dir . "/" . $entry);

            if (!is_dir($entry_path)) {
                continue;
            }

            // Special case. Check for mount points that
	    // aren't the users.
	    $fs_command = self::FS_BINARY . " ls " .
                          escapeshellarg($entry_path) .
			  " 2>&1";
	    $fs_output = shell_exec( $fs_command );

	    $regex = "/is a mount point for volume '(.*)'/";
	    if(preg_match( $regex, $fs_output, $matches )) {
		if ($matches[1] != "#user.$this->uniqname") {
		    continue;
		}
	    }

            if (is_link($entry_path)) {
                continue;
            }

	    if (!$this->walk_dirs($entry_path,
                                  $group,
				  $callback)) {
		closedir($dir_handle);
		return false;
	    }
        }

	closedir($dir_handle);
        return true;

    }

    // walk_dirs test function
    private function echo_dir($dir, $group) {
        echo "$group: $dir\n";
        return true;
    }

    private function group_is_permitted($dir, $group) {

        $group = $this->regex_escape($group);

	$fs_command = self::FS_BINARY . " la " . escapeshellarg($dir);

	$fs_output = shell_exec( $fs_command );

	$regex = "/$group rlidwka/";

        return preg_match( $regex, $fs_output );
    }

    private function permit_group($dir, $group) {
        return $this->change_perms_group($dir, $group, "rlidwka");
    }

    private function unpermit_group($dir, $group) {
        return $this->change_perms_group($dir, $group, "none");
    }

    private function change_perms_group($dir, $group, $perms) {

        if ( strlen( $perms ) == 0) {
            return false;
        }

	$fs_command = self::FS_BINARY . " sa -dir " .
	              escapeshellarg($dir) .
	              " -acl " .
	              escapeshellarg($group) .
	              " $perms";

	$fs_output = shell_exec( $fs_command );

        if ( strlen( $fs_output )) {
            return false;
        }

        return true;
    }

    // Handle regex escapes... only intended for PTS group names.
    private function regex_escape($string) {
	$string = preg_replace("/\./", "\\.", $string );
	$string = preg_replace("/\:/", "\\:", $string );
	return $string;
    }

    // Returns true if the the support group is in supportgroups.
    private function is_support_group($groupname)
    {
	if (empty($groupname)) {
	    return false;
	}

	foreach($this->supportgroups as $supportgroup) {
	    if($supportgroup["name"] == $groupname) {
		return true;
	    }
	}

	return false;
    }

    // Return the array of unique support groups.
    // Note that is a subset of "supportgroups" which
    // actually contains all affiliation->supportgroup mappings.
    public function get()
    {
	$sg = array();
	foreach($this->supportgroups as $supportgroup) {
	    if ($supportgroup["affiliated"] &&
	        !$this->group_in_array($supportgroup["name"], $sg)) {
		$sg[] = $supportgroup;
	    }
	}

	return $sg;
    }

    // Returns true if groupname is in a.
    private function group_in_array($groupname, $a)
    {
	foreach($a as $member) {
	    if ($member["name"] == $groupname) {
		return true;
	    }
	}

	return false;
    }

    public function get_affiliations()
    {
	return $this->affiliations->get();
    }

    // Return the full array of mappings.
    public function get_mappings()
    {
	return $this->supportgroups;
    }

    public function delete_mapping($id)
    {
	if (!$this->is_admin()) {
	    return false;
	}

	$query = "delete from affiliations where id = $id;";

	$result = $this->db_query($query);

        $this->update_authorized();
	$this->update_supportgroups();
	$this->update_permitted();

	return true;
    }

    public function add_mapping($affiliation, $supportgroup)
    {
	if (!$this->is_admin()) {
	    return false;
	}

	// Verify that supportgroup is a legitimate pts group.

	$pts_command = self::PTS_BINARY . " examine " .
		      $supportgroup;
	$pts_output = shell_exec( $pts_command );

	$regex = "/id: (-?\d+)/";
	if(!preg_match( $regex, $pts_output, $matches )) {
	    return false;
	}
	$id = $matches[1];

	$query = "insert into affiliations " .
		 '(name, supportgroup_name, submitter) ' .
		 'values (' .
		 "'" . addslashes($affiliation) . "', " .
		 "'" . addslashes($supportgroup) . "', " .
		 "'" . $this->uniqname . "'" .
		 ");";

	$result = $this->db_query($query);

        $this->update_authorized();
	$this->update_supportgroups();
	$this->update_permitted();

	return true;
    }

    private function db_connect_ro()
    {
	$this->db_connect($this->db_ro_user, $this->db_ro_password);
    }

    private function db_connect($username, $password)
    {
	$this->conn = mysql_connect($this->db_host, $username, $password)
		or die ("Could not connect to Database");

	$this->db = mysql_select_db($this->db_name) or
	      die ("Could not select database $db_name");
    }

    /*
     * mysql_query wrapper with error handling and
     * verbose printing.
     */
    private function db_query($query = "")
    {
	global $debug_db_verbose;

	if (empty($query)) {
	    return FALSE;
	}

	if (!empty($debug_db_verbose)) {
	    print "<pre>$query</pre>\n";
	}

	$result = mysql_query($query)
		  or die("sql query failed: " .
			 "<li>errorno=".mysql_errno() .
			 "<li>error=".mysql_error() .
			 "<li>query=".$query
		  );
	return $result;
    }

    // Returns true if uniqname is on the support administration list.
    public function is_admin()
    {
	return isset($this->authorized[$this->uniqname]);
    }

}

?>
