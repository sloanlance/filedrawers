<?php
/* $Revision: $
 *
 * Copyright (c) 2011 Regents of the University of Michigan.
 * All rights reserved.
 */

class Model_File extends Filedrawers_Filesystem_URL {
    protected $scheme = 'file';

    protected function _rmdir( $url )
    {
        $url_parts = parse_url( $url );
        return rmdir( $url_parts[ 'path' ] );
    }
}
