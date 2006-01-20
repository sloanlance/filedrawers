<?php

/*
 * Copyright (c) 2005 Regents of The University of Michigan.
 * All Rights Reserved.  See COPYRIGHT.
 */  

class Supportgroups {


    public $uniqname = null;
    private $homedir = null;
    private $supportgroups = null;
    private $mappings = null;

    private $db_host;
    private $db_name;
    private $db_ro_user;
    private $db_ro_password;

    private $db = null;
    private $conn = null;

    private $affiliations = null;
    private $authorized = null;

    private $use_logging = false;

    private $logdir = null;

    private $logfile_ispermit = null;
    private $logfile_permit = null;
    private $logfile_unpermit = null;

    private $loghandle_ispermit = null;
    private $loghandle_permit = null;
    private $loghandle_unpermit = null;

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

        global $sdb_host;
        global $sdb_name;
        global $sdb_ro_user;
        global $sdb_ro_password;
        global $allow_support_logging;

        $this->db_host = $sdb_host;
        $this->db_name = $sdb_name;
        $this->db_ro_user = $sdb_ro_user;
        $this->db_ro_password = $sdb_ro_password;

        $this->uniqname = $_SERVER['REMOTE_USER'];
        $this->homedir = $this->get_homedir();

        $this->affiliations  = new Affiliations();

        // Connect to database
        $this->db_connect_ro();

        if ($allow_support_logging == "yes" ) {
            $this->use_logging = true;
        }

        // Create a log file name for this user for this transaction
        $this->logdir =
            "/usr/local/projects/mfile-dev/html-ssl/support-logs";
        $this->logfile_ispermit = $this->logdir . "/$this->uniqname" .
                                  ".ispermit" ;
        $this->logfile_permit = $this->logdir . "/$this->uniqname" .
                                ".permit" ;
        $this->logfile_unpermit = $this->logdir . "/$this->uniqname" .
                                  ".unpermit" ;

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
        $this->mappings = array();

        // Create an array of all known support group mappings.
        $query = "select * from affiliations order by name;";
        $result = $this->db_query($query);

        while ($row = mysql_fetch_array($result)) {
            $temp_group = array();
            $temp_group["id"] = $row["id"];
            $key = $temp_group["name"] = $row["supportgroup_name"];
            $temp_group["affiliation_name"] = $row["name"];
            $temp_group["submitter"] = $row["submitter"];
            $temp_group["affiliated"] =
                    $this->is_affiliated($temp_group["affiliation_name"]);
            $this->mappings[] = $temp_group;

            unset($temp_group["affiliation_name"]);
            unset($temp_group["submitter"]);
            unset($temp_group["id"]);

            // Only add to the supportgroups list if the mapping isn't
            // already present in the supportgroups list.
            if (isset($this->supportgroups[$key])) {
                if ($temp_group["affiliated"] == true) {
                    $this->supportgroups[$key]["affiliated"] = true;
                }
            } else {
                $this->supportgroups[$key] = $temp_group;
            }

        }

    }

    /*
     * Update the "permitted" flag on every support group.
     */
    private function update_permitted()
    {
        $this->logopen_ispermit();

        foreach($this->supportgroups as $key => $garbage) {
            $this->supportgroups[$key]["permitted"] =
                    $this->is_permitted($this->supportgroups[$key]["name"]);

            $this->logwrite_ispermit("is_permitted: " .
                   $this->supportgroups[$key]["name"] . " " .
                   (($this->supportgroups[$key]["permitted"] ==1) ?
                   "permitted." : "not permitted.") . "\n");
        }

        $this->logclose_ispermit();
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
     * Returns true if dirname is administerable (l and a flags
     * explicitly set for uniqname)
     */
    private function is_administerable($dirname, $logfunc)
    {
        $this->uniqname;

        $fs_command = self::FS_BINARY . " la " .
                      escapeshellarg($dirname) .
                      " 2>&1";
        $fs_output = shell_exec( $fs_command );
       
        // Isolate Normal rights
        $regex = "/^Normal rights:(.*)(?:Negative rights:)*/ms";
        if(!preg_match( $regex, $fs_output, $matches )) {

            call_user_func($logfunc,
                           "is_administerable: no match for rights\n");
            call_user_func($logfunc, "is_administerable: $dirname false\n");

            return false;
        }

        $rights = $matches[1];

        $regex = "/^\s+$this->uniqname ([wildkar]{1,7})/ms";
        if(!preg_match( $regex, $rights, $matches )) {

            call_user_func($logfunc,
                   "is_administerable: no match for user right\n");
            call_user_func($logfunc,
                   "is_administerable: $dirname false\n");

            return false;
        }

        if ((strrpos($matches[1], 'a')===false) ||
           (strrpos($matches[1], 'l')===false)) {

            call_user_func($logfunc, "is_administerable: $dirname false\n");

            return false;
        }

        call_user_func($logfunc, "is_administerable: $dirname true\n");

        return true;
    }

    /*
     * Returns true if dirname is on the user's volume
     */
    private function is_on_uservolume($dirname, $logfunc)
    {
        $this->uniqname;

        $fs_command = self::FS_BINARY . " examine " .
                      escapeshellarg($dirname) .
                      " 2>&1";
        $fs_output = shell_exec( $fs_command );

        $regex = "/^Volume status for vid \= \d+ named " .
                 "(user.$this->uniqname)$/sm";
        if(preg_match( $regex, $fs_output, $matches )) {
            call_user_func($logfunc, "is_on_uservolume: $dirname true\n");
            return true;
        } else {
            call_user_func($logfunc, "is_on_uservolume: $dirname false\n");
            return false;
        }
    }

    /*
     * Check to see if the given PTS group has wildkar permissions
     * over the users entire AFS home directory.
     */
    private function is_permitted($groupname)
    {
        $walked = array();

        $rc = $this->walk_dirs($this->homedir,
                               $groupname,
                               array("Supportgroups",
                                     "group_is_permitted"),
                               $walked,
                               array("Supportgroups",
                                     "logwrite_ispermit"));

        return $rc;
    }

    /*
     * Give the given PTS group has wildkar permissions
     * over the users entire AFS home directory.
     */
    public function give_permissions($groupname)
    {
        $walked = array();

        if (!$this->is_support_group($groupname)) {
            return false;
        }

        $this->logopen_permit();

        $rc = $this->walk_dirs($this->homedir,
                              $groupname,
                              array("Supportgroups",
                                    "permit_group"),
                              $walked,
                              array("Supportgroups",
                                    "logwrite_permit")
                              );

        $this->logwrite_permit("give_permissions: " .
               "$groupname " .
               ($rc) ? "succeeded" : "failed" .
               ".\n");
        $this->logclose_permit();

        $this->update_permitted();

        return $rc;
    }

    /*
     * Remove the given PTS group's permissions
     * over the users entire AFS home directory.
     */
    public function remove_permissions($groupname)
    {
        $walked = array();

        if (!$this->is_support_group($groupname)) {
            return false;
        }

        $this->logopen_unpermit();

        $rc = $this->walk_dirs($this->homedir,
                                $groupname,
                                array("Supportgroups",
                                      "unpermit_group"),
                              $walked,
                              array("Supportgroups",
                                    "logwrite_unpermit")
                              );

        $this->logwrite_unpermit(
           "remove_permissions: $groupname " .
           ($rc) ? "succeeded" : "failed" .
           ".\n");
        $this->logclose_unpermit();

        $this->update_permitted();

        return $rc;
    }

    /*
     * Walk directories recursively.
     * call "callback" on every found directory.
     *
     * Returns 1 when all "callback" return true.
     *
     * Returns -1 when any "callback" returns false.
     *
     * Returns 0 when there are no administratable directories
     * present under dir.
     */
    private function walk_dirs($dir,
                               $group,
                               $callback,
                               &$walked,
                               $logfunc)
    {

        // We're not concerned with non-directory entries
        if (!is_dir($dir)) {
            return 0;
        }

        // Have we been in this directory before?
        if (($dirstat = stat($dir)) == FALSE) {
            return -1;
        }

        $dirinode = $dirstat['dev'] . "." . $dirstat['ino'];

        if (in_array($dirinode,$walked)) {
            return 0;
        }

        // Add the current directory device and inode to the array of
        // walked directories
        $walked[] = $dirinode;

        // We don't want to follow links
        if (is_link($dir)) {
            return 0;
        }

        /*
         * If the user doesn't have explicit lookup and administer
         * rights on that directory, do not decend into it.
         * scam XXX remove this test for now.
         */

        /*
        if (!$this->is_administerable($dir, $logfunc)) {
            return 0;
        }
         */

        /*
         * If the directory isn't on the user's volume, do not decend into it.
         */
        if (!$this->is_on_uservolume($dir, $logfunc)) {
            return 0;
        }

        $result = call_user_func($callback, $dir, $group, $logfunc);

        if ($result == -1) {
            return -1;
        }

        // decend into any subdirectories

        /*
         * If opendir fails after we've determined that we are supposed
         * to have lookup permissions on this directory, this is an
         * error which should be logged.
         */
        if (!($dir_handle = @opendir ($dir))) {
            return 0;
        }

        while ($entry=readdir($dir_handle)) {
            // Skip "." and ".." dir entries.
            if ($entry=="." or $entry=="..") {
                continue;
            }

            $entry_path = ($dir . "/" . $entry);

            if ($this->walk_dirs($entry_path,
                                  $group,
                                  $callback,
                                  $walked,
                                  $logfunc) == -1) {
                closedir($dir_handle);
                return -1;
            }
        }

        closedir($dir_handle);
        return 1;

    }

    // walk_dirs test function
    private function echo_dir($dir, $group) {
        echo "$group: $dir\n";
        return true;
    }

    private function group_is_permitted($dir, $group, $logfunc) {

        $group = $this->regex_escape($group);

        $fs_command = self::FS_BINARY . " la " . escapeshellarg($dir);

        $fs_output = shell_exec( $fs_command );

        // Isolate Normal rights
        $regex = "/^Normal rights:(.*)(?:Negative rights:)*/ms";
        if(!preg_match( $regex, $fs_output, $matches )) {
            call_user_func($logfunc,
                           "group_is_permitted: no match for rights\n");
            call_user_func($logfunc,
                           "group_is_permitted: $dirname false\n");
            return -1;
        }

        $rights = $matches[1];

        $regex = "/^\s+$group [wildkar]{7}/ms";
        if(!preg_match( $regex, $rights, $matches )) {
            call_user_func($logfunc, "group_is_permitted: $dir $group false\n");
            return -1;
        }

        call_user_func($logfunc, "group_is_permitted: $dir $group true\n");
        return 1;
    }

    private function permit_group($dir, $group) {
        return $this->change_perms_group($dir, $group, "rlidwka");
    }

    private function unpermit_group($dir, $group) {
        return $this->change_perms_group($dir, $group, "none");
    }

    private function change_perms_group($dir, $group, $perms) {

        if ( strlen( $perms ) == 0) {
            return -1;
        }

        $fs_command = self::FS_BINARY . " sa -dir " .
                      escapeshellarg($dir) .
                      " -acl " .
                      escapeshellarg($group) .
                      " $perms";

        $fs_output = shell_exec( $fs_command );

        if ( strlen( $fs_output )) {
            return -1;
        }

        return 1;
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

    // Return the array of unique support groups that are either
    // affiliated or permitted.
    public function get()
    {
        $sg = array();

        foreach($this->supportgroups as $supportgroup) {
            if(($supportgroup["affiliated"] == true) ||
               ($supportgroup["permitted"] == 1)) {
                $sg[] = $supportgroup;
            }
        }

        return $sg;
    }

    public function get_affiliations()
    {
        return $this->affiliations->get();
    }

    // Return the full array of mappings.
    public function get_mappings()
    {
        return $this->mappings;
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

    // Functions to log allow-admin operations.

    private function logopen_ispermit()
    {
        $this->logopen($this->logfile_ispermit, $this->loghandle_ispermit);

        return;
    }

    private function logwrite_ispermit($entry)
    {
        $this->logwrite($this->loghandle_ispermit, $entry);

        return;
    }

    private function logclose_ispermit()
    {
        $this->logclose($this->loghandle_ispermit);

        return;
    }

    private function logopen_permit()
    {
        $this->logopen($this->logfile_permit, $this->loghandle_permit);

        return;
    }

    private function logwrite_permit($entry)
    {
        $this->logwrite($this->loghandle_permit, $entry);

        return;
    }

    private function logclose_permit()
    {
        $this->logclose($this->loghandle_permit);

        return;
    }

    private function logopen_unpermit()
    {
        $this->logopen($this->logfile_unpermit, $this->loghandle_unpermit);

        return;
    }

    private function logwrite_unpermit($entry)
    {
        $this->logwrite($this->loghandle_unpermit, $entry);

        return;
    }

    private function logclose_unpermit()
    {
        $this->logclose($this->loghandle_unpermit);

        return;
    }

    private function logopen($filename, &$handle)
    {
        if ($this->use_logging) {
            $handle = fopen($filename, 'w');
        }

        return;
    }

    private function logwrite($handle, $entry)
    {
        if ($this->use_logging) {
            fwrite($handle, $entry);
        }
    }

    private function logclose($handle)
    {
        if ($this->use_logging) {
            fclose($handle);
        }

        return;
    }
}

?>
