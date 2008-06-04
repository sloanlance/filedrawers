<?php
/**
 * A class to manage session data inside a MySQL database.
 *
 * Copyright (c) 2008 Regents of the University of Michigan.
 * All rights reserved.
 *
 * @copyright  2008 Regents of the University of Michigan
 * @license    http://filedrawers.org/license.php
 * @version    $Revision: 1.3 $
 * @link       http://filedrawers.org
 * @since      File available since Release 0.4.2
 */
class Session
{
    private $db;
    private $maxLifeTime;
    
    public function __construct()
    {
        $this->maxLifeTime = get_cfg_var( 'session.gc_maxlifetime' );

        session_set_save_handler(
                array( &$this, 'open' ),
                array( &$this, 'close' ),
                array( &$this, 'read' ),
                array( &$this, 'write' ),
                array( &$this, 'destroy' ),
                array( &$this, 'gc' ));
    }


    // Create a connection to a database
    private function dbConnect()
    {
        $this->db = new mysqli( DB_HOST, DB_USER, DB_PASS, DB_NAME );

        if ( mysqli_connect_errno()) {
            return false;
        } else {
            return true;
        }
    }


    // Open session - input params are not used
    public function open( $path, $name )
    {
        $this->dbConnect();

        return true;
    }


    // Close session (for a manual call of the session gc function)
    public function close()
    {
        $this->gc( 0 );
        return true;
    }

    
    // Read session data from database
    public function read( $sessionID )
    {
        $sesValue = '';

        if ( !$stmt = $this->db->prepare( "SELECT session_data FROM sessions "
                . "WHERE session_id = ?" )) {
            return '';
        }

        $stmt->bind_param( 's', $sessionID );
        $stmt->execute();
        $stmt->bind_result( $sessionData );
        $stmt->fetch();
        $stmt->close();

        return $sessionData;
    }


    // Write new data to database
    public function write( $sessionID, $sessionData )
    {
        $time = time() + $this->maxLifeTime;

        if ( !$stmt = $this->db->prepare( "UPDATE sessions SET " .
                "session_data=?, expires=? WHERE session_id=?" )) {
            error_log( 'Filedrawers session DB: ' . $this->db->error );
            return false;
        }

        $stmt->bind_param( 'sss', $sessionData, $time, $sessionID );
        $stmt->execute();

        if ( $stmt->affected_rows > 0 ) {
            $stmt->close();
            return true;
        }

        if ( !$stmt = $this->db->prepare( "INSERT INTO sessions"
                . " (session_id, session_data, expires)"
                . " VALUES (?, ?, ?)" )) {
            error_log( 'Filedrawers session DB: ' . $this->db->error );
            return false;
        }
        
        $stmt->bind_param( 'sss', $sessionID, $sessionData, $time);
        $stmt->execute();
        $stmt->close();

        return true;
    }


    // Destroy session record in database
    public function destroy( $sessionID )
    {
        if ( !$stmt = $this->db->prepare( "DELETE FROM sessions "
                . "WHERE session_id=?" )) {
            return false;
        }

        $stmt->bind_param( 's', $sessionID );

        if ( !$stmt->execute()) {
            return false;
        }

        $stmt->close();

        return true;
    }


    // Garbage collection, delete old sessions - input param is not used
    public function gc( $life )
    {
        if ( !$stmt = $this->db->prepare( "DELETE FROM sessions "
                . "WHERE expires < ?" )) {
            return false;
        }

        $stmt->bind_param( 's', time());

        if ( !$stmt->execute()) {
            return false;
        }

        $stmt->close();

        return true;
    }


    public function __destruct()
    {
        // Ensures that the session data is stored before the database object
        // is destroyed. See: http://bugs.php.net/bug.php?id=33772
        @session_write_close();
        $this->db->close();
    }
}

