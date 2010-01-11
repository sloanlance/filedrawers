<?php
/* $Revision: $
 *
 * Copyright (c) 2008 Regents of the University of Michigan.
 * All rights reserved.
 */

class Request
{
    protected $path;

    public function __construct()
    {
        // CSRF Protection
        session_start();
        if ( !isset( $_SESSION['formKey'] )) {
            $_SESSION['formKey'] = md5( uniqid( rand(), true ));
        }

        /*if ( ! empty( $_POST )) {
            if ( ! isset( $_POST['formKey'] ) || $_SESSION['formKey'] !=
                    $_POST['formKey'] ) {
                // throw exception CSRF protection error
                exit( 'throw exception CSRF protection error' );
            }
        }*/

        $this->get  = $_GET;
        $this->post = $_POST;
    }


    public function fetchCSRFvalue()
    {
        return $this->checkValue( $this->post[$name], $regEx );
    }


    public function fetchGetValue($name, $regEx)
    {
        if ( ! isset($this->get[$name])) {
            return null;
        }

        if ( is_array( $this->get[$name] )) {
            $values = array();

            foreach ( $this->get[$name] as $key => $value ) {
                $values[$key] = $this->checkValue( $name, $regEx, $this->get );
            }
        }

        return $this->checkValue( $name, $regEx, $this->get );
    }


    public function fetchPostValue( $name, $regEx )
    {
        return $this->checkValue( $this->post[$name], $regEx );
    }


    public function fetchRawPostVal($name)
    {
        return $this->post[$name];
    }
    
    
    public function test()
    {
        return $this->post;
    }


    private function checkValue( $name, $regEx, $source )
    {
        $regEx = '/^' . $regEx . '$/';

        if ( ! isset( $source[$name] )) {
            return null;
        }
        
        if ( strlen( $source[$name] ) > 1024 ) {
            return false;
        }

        if ( $regEx === 'path' || preg_match( $regEx, $source[$name] ) === 1 ) {
            return trim( $source[$name] );
        }

        return false;
    }
}

