#ifdef HAVE_CONFIG_H
#include "config.h"
#endif /* HAVE_CONFIG_H */

#include <sys/types.h>
#include <sys/stat.h>
#include <errno.h>
#include <fcntl.h>
#include <stdio.h>
#include <stdlib.h>

#include "php.h"
#include "php_filedrawers.h"

extern int			errno;

static zend_function_entry	filedrawers_functions[] = {
    ZEND_FE( filedrawers_rename, NULL )
    ZEND_FE( filedrawers_unlink, NULL )
    { NULL, NULL, NULL }
};

zend_module_entry		filedrawers_module_entry = {
    STANDARD_MODULE_HEADER,
    PHP_FILEDRAWERS_EXTNAME,
    filedrawers_functions,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    PHP_FILEDRAWERS_VERSION,
    STANDARD_MODULE_PROPERTIES
};

#ifdef COMPILE_DL_FILEDRAWERS
ZEND_GET_MODULE( filedrawers )
#endif /* COMPILE_DL_FILEDRAWERS */

ZEND_FUNCTION( filedrawers_rename )
{
    struct stat	st, tst;
    dev_t	fsdev, srcdev, dstdev;
    char	*src, *dst, *fs, *p;
    char	rsrc[ MAXPATHLEN ], rdst[ MAXPATHLEN ];
    char	*dstdir = NULL;
    char	*srcfile;
    int		slen, dlen, flen;
    int		dfd;
    int		success = 0;

    if ( zend_parse_parameters( ZEND_NUM_ARGS() TSRMLS_CC,
				"sss", &src, &slen, &dst, &dlen,
				&fs, &flen ) == FAILURE ) {
	RETURN_FALSE;
    }

    if ( VCWD_STAT( fs, &st ) != 0 ) {
	zend_error( E_WARNING, "filedrawers_rename: VCWD_STAT %s: %s\n",
			fs, strerror( errno ));
	RETURN_FALSE;
    }
    fsdev = st.st_dev;

    if ( realpath( src, rsrc ) == NULL ) {
	zend_error( E_WARNING, "filedrawers_rename: realpath %s: %s\n",
			src, strerror( errno ));
	RETURN_FALSE;
    }
    if (( srcfile = strrchr( rsrc, '/' )) == NULL ) {
	zend_error( E_WARNING, "filedrawers_rename: "
			"no / in path after realpath!\n" );
	RETURN_FALSE;
    }
    *srcfile++ = '\0';
    if ( *srcfile == '\0' ) {
	zend_error( E_WARNING, "filedrawers_rename: invalid source path\n" );
	RETURN_FALSE;
    }

    if (( dfd = open( ".", O_RDONLY )) < 0 ) {
	zend_error( E_WARNING, "filedrawers_rename: open .: %s\n",
			strerror( errno ));
	RETURN_FALSE;
    }

    /*
     * we change to the srcfile's parent dir and make
     * sure it's on the valid fs to prevent an attacker
     * from performing a local-to-local rename
     * (suggested by Simon Wilkinson). we later fchdir
     * back to the original working directory.
     */
    if ( chdir( rsrc ) != 0 ) {
	zend_error( E_WARNING, "filedrawers_rename: chdir %s: %s\n",
			rsrc, strerror( errno ));
	goto rename_cleanup;
    }
    if ( VCWD_STAT( ".", &st ) != 0 ) {
	zend_error( E_WARNING, "filedrawers_rename: VCWD_STAT .: %s\n",
			strerror( errno ));
	goto rename_cleanup;
    }
    if ( st.st_dev != fsdev ) {
	zend_error( E_WARNING, "filedrawers_rename: %s/%s is not on %s\n",
			rsrc, srcfile, fs );
	goto rename_cleanup;
    }

    if ( VCWD_STAT( dst, &st ) != 0 ) {
	if ( errno != ENOENT ) {
	    zend_error( E_WARNING, "filedrawers_rename: VCWD_STAT %s: %s\n",
			dst, strerror( errno ));
	    goto rename_cleanup;
	}

	if (( dstdir = estrndup( dst, dlen )) == NULL ) {
	    zend_error( E_WARNING, "estrndup dstdir failed" );
	    goto rename_cleanup;
	}
	if (( p = strrchr( dstdir, '/' )) == NULL ) {
	    efree( dstdir );
	    if (( dstdir = estrndup( ".", 1 )) == NULL ) {
		zend_error( E_WARNING, "estrndup \".\" failed" );
		goto rename_cleanup;
	    }
	    p = dst;
	} else {
	    *p++ = '\0';
	    if ( *p == '\0' ) {
		zend_error( E_WARNING, "invalid destination path" );
		goto rename_cleanup;
	    }
	}
	if ( realpath( dstdir, rdst ) == NULL ) {
	    zend_error( E_WARNING, "filedrawers_rename: realpath %s: %s\n",
			    dstdir, strerror( errno ));
	    goto rename_cleanup;
	}
	if ( VCWD_STAT( rdst, &st ) != 0 ) {
	    zend_error( E_WARNING, "filedrawers_rename: VCWD_STAT %s: %s\n",
			    rdst, strerror( errno ));
	    goto rename_cleanup;
	}
	/* +2 for '/' and NUL-termination */
	if (( strlen( rdst ) + strlen( p ) + 2 ) >= sizeof( rdst )) {
	    zend_error( E_WARNING, "resolved path too long" );
	    goto rename_cleanup;
	}
	strcat( rdst, "/" );
	strcat( rdst, p );
	
	/* one last check to make sure they didn't sneak a link in on us. */
	VCWD_STAT( rdst, &tst );
	if ( errno != ENOENT ) {
	    zend_error( E_WARNING, "fishy destination, not calling rename" );
	    goto rename_cleanup;
	}
    } else if ( realpath( dst, rdst ) == NULL ) {
	zend_error( E_WARNING, "filedrawers_rename: realpath %s: %s\n",
			dst, strerror( errno ));
	goto rename_cleanup;
    }

    if ( st.st_dev != fsdev ) {
	zend_error( E_WARNING, "filedrawers_rename: %s is not on %s\n",
			dst, fs );
	goto rename_cleanup;
    }

    /*
     * most implementations will return EXDEV if src and dst are not
     * on the same device. we rely on that as the real safeguard
     * against local-to-net and net-to-local race conditions.
     */
    if ( rename( srcfile, rdst ) != 0 ) {
	zend_error( E_WARNING, "filedrawers_rename: rename %s to %s: %s\n",
			rsrc, rdst, strerror( errno ));
	goto rename_cleanup;
    }
    success = 1;

rename_cleanup:
    if ( dstdir != NULL ) {
	efree( dstdir );
    }
    if ( fchdir( dfd ) != 0 ) {
	zend_error( E_WARNING, "filedrawers_rename: fchdir: %s\n",
			strerror( errno ));
    }
    if ( close( dfd ) != 0 ) {
	zend_error( E_WARNING, "filedrawers_rename: close: %s\n",
			strerror( errno ));
    }
    if ( success == 1 ) {
	RETURN_TRUE;
    }

    RETURN_FALSE;
}

ZEND_FUNCTION( filedrawers_unlink )
{
    struct stat		st;
    dev_t		fsdev;
    char		*ufile, *fs, *p;
    char		*udir = NULL;
    int			uflen, flen;
    int			dfd;
    int			success = 0;

    if ( zend_parse_parameters( ZEND_NUM_ARGS() TSRMLS_CC,
		"ss", &ufile, &uflen, &fs, &flen ) == FAILURE ) {
	RETURN_FALSE;
    }

    if ( VCWD_STAT( fs, &st ) != 0 ) {
	zend_error( E_WARNING, "filedrawers_unlink: VCWD_STAT %s: %s\n",
			fs, strerror( errno ));
	RETURN_FALSE;
    }
    fsdev = st.st_dev;

    if (( dfd = open( ".", O_RDONLY )) < 0 ) {
	zend_error( E_WARNING, "filedrawers_unlink: open .: %s\n",
			strerror( errno ));
	RETURN_FALSE;
    }

    if (( udir = estrndup( ufile, uflen )) == NULL ) {
	zend_error( E_WARNING, "filedrawers_unlink: estrndup %s: %s\n",
			ufile, strerror( errno ));
	goto unlink_cleanup;
    }
    if (( p = strrchr( udir, '/' )) == NULL ) {
	efree( udir );
	if (( udir = estrndup( ".", 1 )) == NULL ) {
	    zend_error( E_WARNING, "filedrawers_unlink: estrndup . failed\n" );
	    goto unlink_cleanup;
	}
	p = ufile;
    } else {
	*p++ = '\0';
	if ( *p == '\0' ) {
	    zend_error( E_WARNING, "filedrawers_unlink: invalid unlink path\n");
	    goto unlink_cleanup;
	}
    }

    if ( chdir( udir ) != 0 ) {
	zend_error( E_WARNING, "filedrawers_unlink: chdir %s: %s\n",
			udir, strerror( errno ));
	goto unlink_cleanup;
    }
    if ( VCWD_STAT( ".", &st ) != 0 ) {
	zend_error( E_WARNING, "filedrawers_unlink: VCWD_STAT .: %s\n",
			strerror( errno ));
	goto unlink_cleanup;
    }
    if ( st.st_dev != fsdev ) {
	zend_error( E_WARNING, "filedrawers_unlink: %s is not on %s\n",
			ufile, fs );
	goto unlink_cleanup;
    }

    if ( unlink( p ) != 0 ) {
	zend_error( E_WARNING, "filedrawers_unlink: unlink %s: %s\n",
			p, strerror( errno ));
	goto unlink_cleanup;
    }
    success = 1;

unlink_cleanup:
    if ( udir != NULL ) {
	efree( udir );
    }
    if ( fchdir( dfd ) != 0 ) {
	zend_error( E_WARNING, "filedrawers_unlink: fchdir: %s\n",
			strerror( errno ));
    }
    if ( close( dfd ) != 0 ) {
	zend_error( E_WARNING, "filedrawers_unlink: close: %s\n",
			strerror( errno ));
    }

    if ( success ) {
	RETURN_TRUE;
    }

    RETURN_FALSE;
}
