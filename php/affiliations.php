<?php

/*
 * Copyright (c) 2005 Regents of The University of Michigan.
 * All Rights Reserved.  See COPYRIGHT.
 */  

class Affiliations {

    public $uniqname = null;
    public $affiliations = null;

    const LDAP_HOST = "ldap.itd.umich.edu";
    const LDAP_PORT = "389";

    const LDAP_BINARY = "/usr/bin/ldapsearch";

    const AFFILIATION_PATTERN = '/^ou: (.*)$/';

    /*
     * Creates a new set of affiliations.
     */
    function __construct()
    {
	$error_msg = null;
	$dir = null;

        $this->uniqname = $_SERVER['REMOTE_USER'];
	$this->affiliations = array();

	$ldap_command = self::LDAP_BINARY . " -x -LLL " .
			"-h " . self::LDAP_HOST . " " .
			"-p " . self::LDAP_PORT . " " .
			"uid=\"" . $this->uniqname . "\" " .
			"ou";

	$ldap_output = shell_exec( $ldap_command );

	// Parse LDAP output for affiliations.
	$ldap_lines = explode("\n", $ldap_output);

	foreach ($ldap_lines as $line) {
	    if (preg_match(self::AFFILIATION_PATTERN,
			   trim(array_shift($ldap_lines)),
			   $matches)) {
		$this->affiliations[] = array("name" => $matches[1]);
	    }
	}

	return;
    }

    // Return the array of affiliations
    public function get()
    {
	$a = $this->affiliations;
	return $a;
    }

}

?>
