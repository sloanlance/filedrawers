<?php
/* $Revision: $
 *
 * Copyright (c) 2011 Regents of the University of Michigan.
 * All rights reserved.
 */

class Filedrawers_Filesystem_Url_File extends Filedrawers_Filesystem_Url {
    protected $scheme = 'file';

    protected function _rmdir( $url )
    {
        $url_parts = parse_url( $url );
        return rmdir( $url_parts[ 'path' ] );
    }

    public function getHomedir()
    {
        return Filedrawers_Filesystem::getHomedir();
    }


}
