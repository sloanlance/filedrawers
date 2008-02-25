/*
 * Copyright (c) 2008 Regents of The University of Michigan.
 * All Rights Reserved.  See COPYRIGHT.
 */

#include <sys/types.h>
#include <sys/param.h>
#include <sys/stat.h>
#include <ctype.h>
#include <errno.h>
#include <fcntl.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>

#include <cgi.h>

#ifndef SESSION_PATH
# define SESSION_PATH		"/tmp"
#endif /* SESSION_PATH */

#ifndef AFS_VALID_PATH
# define AFS_VALID_PATH		"/afs/"
#endif /* AFS_VALID_PATH */

int		upload_init( char **, struct cgi_list * );
int		upload_progress( char *, int );

static void	progress_complete( void );
static void	st_free( void );

extern int	errno;

struct cgi_list cl[] = {
#define CL_SESSIONID		0
    { "sessionid", CGI_TYPE_STRING, NULL },
#define CL_PATH			1	
    { "path", CGI_TYPE_STRING, NULL },
#define CL_RETURNURI		2
    { "returnToURI", CGI_TYPE_STRING, NULL },
#define CL_FILE			3	
    { "file", CGI_TYPE_FILE, NULL },
#define CL_CLOBBER		4	
    { "overwrite_file", CGI_TYPE_STRING, NULL },
    { NULL, CGI_TYPE_UNDEF, NULL },
};

struct status {
    FILE		*s_fp;	
    char		*s_sid;
    char		*s_uri;
    long int		s_content_len;
    long int		s_bytes_uploaded;
    long int		s_total_bytes_uploaded;
};

struct status 		*st;
static char     	*ref_error = _URL_ERROR_PAGE;
char            	*user = NULL;
char			*fdtmpdir;
char			*validpath;

/* #define FILEDRAWERS_UPLOAD_CGI_DEBUG */
#ifdef FILEDRAWERS_UPLOAD_CGI_DEBUG
#include <stdarg.h>

    static void
debug( char *fmt, ... )
{
    va_list		va;

    fputs( "debug: ", stderr );

    va_start( va, fmt );
    vfprintf( stderr, fmt, va );
    va_end( va );

    fflush( stderr );
}
#else /* !FILEDRAWERS_UPLOAD_CGI_DEBUG */
#define debug( x, ... )
#endif /* FILEDRAWERS_UPLOAD_CGI_DEBUG */


    int
upload_init( char **dir, struct cgi_list *cl )
{
    struct stat		s;
    dev_t		vdev;
    char		tmp[ MAXPATHLEN ], rpath[ MAXPATHLEN ];
    char		*p;
    int			fd;
    extern int          cgi_file_clobber;

    if ( cl[ CL_RETURNURI ].cl_data != NULL ) {
	if ( st->s_uri == NULL ) {
	    /* XXX any additional validation in return URI required? */
	    if (( st->s_uri = strdup( cl[ CL_RETURNURI ].cl_data )) == NULL ) {
		perror( "strdup" );
		return( -1 );
	    }
	    debug( "%s: RETURN_URI = %s\n", __FUNCTION__, st->s_uri );
	}
    }

    if ( cl[ CL_SESSIONID ].cl_data != NULL && st->s_sid == NULL ) {
	st->s_content_len = atoi( getenv( "CONTENT_LENGTH" ));

	/* no special path characters allowed in the session ID */
	for ( p = cl[ CL_SESSIONID ].cl_data; *p != '\0'; p++ ) {
	    if ( !isalnum( *p )) {
		fprintf( stderr, "%s: invalid session ID\n",
			( char * )cl[ CL_SESSIONID ].cl_data );
		return( -1 );
	    }
	}

	if ( snprintf( rpath, MAXPATHLEN, "%s/%s", fdtmpdir,
			(char *)cl[ CL_SESSIONID ].cl_data ) >= MAXPATHLEN ) {
	    fprintf( stderr, "%s/%s: too long\n", fdtmpdir,
			(char *)cl[ CL_SESSIONID ].cl_data );
	    return( -1 );
	}
	if (( st->s_sid = strdup( rpath )) == NULL ) {
	    perror( "strdup" );
	    return( -1 );
	}

	/* open the progress file for writing */
	if ( st->s_fp == NULL ) {
	    /* O_EXCL so we can't clobber something else unintentionally */
	    if (( fd = open( st->s_sid,
			    O_WRONLY | O_CREAT | O_EXCL, 0600 )) < 0 ) {
		perror( st->s_sid );
		return( -1 );
	    }
	    if (( st->s_fp = fdopen( fd, "w" )) < 0 ) {
		perror( "fdopen" );
		return( -1 );
	    }
	    st->s_total_bytes_uploaded = 0;

	    debug( "%s: SESSIONID = %s\n", __FUNCTION__, st->s_sid );
	}
    }

    /*
     * data here is the directory where the file will be uploaded.
     * libcgi appends the filename itself.
     */
    if ( cl[ CL_PATH ].cl_data != NULL ) {
	if ( strlen( cl[ CL_PATH ].cl_data ) >= MAXPATHLEN ) {
	    fprintf( stderr, "%s: too long\n", (char *)cl[ CL_PATH ].cl_data );
	    return( -1 );
	}
	strcpy( tmp, cl[ CL_PATH ].cl_data );
	if ( realpath( tmp, rpath ) == NULL ) {
	    fprintf( stderr, "realpath %s: %s\n", tmp, strerror( errno ));
	    return( -1 );
	}
	
	/* make sure we're still in a validpath subdirectory after realpath */
	if ( strncmp( validpath, rpath, strlen( validpath )) != 0 ) {
	    fprintf( stderr, "invalid upload path \"%s\"\n", rpath );
	    return( -1 );
	}

	/* Ensure destination dir device matches validpath's */
	if ( stat( validpath, &s ) != 0 ) {
	    fprintf( stderr, "stat %s: %s\n", validpath, strerror( errno ));
	    return( -1 );
	}
	vdev = s.st_dev;
	if ( stat( rpath, &s ) != 0 ) {
	    fprintf( stderr, "stat %s: %s\n", rpath, strerror( errno ));
	    return( -1 );
	}
	if ( s.st_dev != vdev ) {
	    fprintf( stderr, "%s: prohibited upload destination\n", rpath );
	    return( -1 );
	}

	/* libcgi uses dir as the upload destination directory */
	if (( *dir = strdup( rpath )) == NULL ) {
	    perror( "strdup" );
	    return( -1 );
	}
	debug( "%s: UPLOAD_DIR = %s\n", __FUNCTION__, *dir );
    }

    if ( cl[ CL_FILE ].cl_data != NULL ) {
	debug( "%s: FILE_DATA found\n", __FUNCTION__ );
    }

    if ( cl[ CL_CLOBBER ].cl_data != NULL ) {
	if ( !cgi_file_clobber &&
		strcmp( cl[ CL_CLOBBER ].cl_data, "on" ) == 0 ) {
	    fprintf( stderr, "file clobber enabled\n" );
	    cgi_file_clobber = 1;
	}
    }

    return( 0 );
}

    int
upload_progress( char *filename, int bytes_uploaded )
{
    if ( st->s_sid == NULL ) {
	fprintf( stderr, "error: no progress file\n" );
	return( -1 );
    }

    st->s_total_bytes_uploaded += bytes_uploaded;
    errno = 0;
    rewind( st->s_fp );
    if ( errno ) {
	fprintf( stderr, "rewind: %s\n", strerror( errno ));
	return( -1 );
    }
    fprintf( st->s_fp, "%s:%ld:%ld:\n", filename,
		st->s_content_len, st->s_total_bytes_uploaded );
    fflush( st->s_fp );

    return( 0 );
}


/* free the status struct */
    static void
st_free( void )
{
    if ( st != NULL ) {
	if ( st->s_sid != NULL ) {
	    free( st->s_sid );
	}
	if ( st->s_uri != NULL ) {
	    free( st->s_uri );
	}
	free( st );
    }
}

    static void
progress_complete( void )
{
    struct cgi_file	*cf;
    int			fd = fileno( st->s_fp );

    /* no sense in error checking here. just try to update the file. */
    ( void )ftruncate( fd, 0 );
    rewind( st->s_fp );

    if ( cl[ CL_FILE ].cl_data != NULL ) {
	for( cf = cl[ CL_FILE ].cl_data; cf != NULL; cf = cf->cf_next ) {
	    fprintf( st->s_fp, "%s:finished:%s\n", cf->cf_name, cf->cf_status );
	}
	fflush( st->s_fp );
    }
}

    int
main( int ac, char *av[] )
{
    CGIHANDLE		*cgi;
    struct function	func;
    char        	*agent_string = "did not get browser string";
    char		*dir = NULL;
    char		*prog;

    func.f_init = upload_init;
    func.f_progress = upload_progress;

    if (( prog = strrchr( av[ 0 ], '/' )) == NULL ) {
	prog = av[ 0 ];
    } else {
	prog++;
    }

    if (( agent_string = (char *)getenv( "HTTP_USER_AGENT" )) != NULL ) {
	debug( "%s: using browser %s\n\n", prog, agent_string );
    }

    if (( user = (char *)getenv( "REMOTE_USER" )) == NULL ) {
	fprintf( stderr, "ERROR: %s did not get user name.\n", prog );
	printf( "Location: %s\n\n", ref_error ); 
	exit( 2 );
    }

    /*
     * real configuration would be nice, of course, but 
     * at least this lets the admin configure the server
     * using SetEnv directives. filedrawers itself will
     * have to be told about any settings, though.
     */
    if (( fdtmpdir = getenv( "FILEDRAWERS_SESSION_DIR" )) == NULL ) {
	fdtmpdir = SESSION_PATH;
    }
    if (( validpath = getenv( "FILEDRAWERS_VALID_PATH" )) == NULL ) {
	validpath = AFS_VALID_PATH;
    }

    debug( "cgi_init: getting a cgi handle.\n" );
    if (( cgi = cgi_init()) == NULL ) {
	fprintf( stderr, "ERROR: %s failed to create buffer\n", prog );
	printf( "Location: %s\n\n", ref_error ); 
	exit( 2 );
    }

    if (( st = ( struct status * )malloc( sizeof( struct status ))) == NULL ) {
	perror( "malloc" );
	printf( "Location: %s\n\n", ref_error ); 
	exit( 2 );
    }
    st->s_fp = NULL;
    st->s_sid = NULL;
    st->s_uri = NULL;

    debug( "%s: calling cgi_multipart\n", prog );
    if ( cgi_multipart( cgi, cl, dir, &func ) != 0 ) {
	fprintf( stderr, "ERROR: cgi_multipart failed\n" );
	    /* but did we upload anything ? */
    } else {
	debug( "%s: cgi_multipart successful\n", prog );
    }

    if ( st->s_fp != NULL ) {
	progress_complete();
	fclose( st->s_fp );
    }

    if ( st->s_uri == NULL ) {
	printf( "Location: %s\n\n", ref_error );
        debug( "redirect to: %s\n", ref_error );
    } else {
        printf( "Location: %s\n\n", st->s_uri );
        debug( "redirect to: %s\n", st->s_uri );
    }

    if ( dir != NULL ) {
	free( dir );
    }
    cgi_cl_free( cl );
    cgi_free( cgi );
    st_free();
    fprintf( stderr, "%s exiting\n", prog );

    return( 0 );
}

/*
 * Format of progress (sid) file
 *
 * during upload there is one line, format is -
 * filename:content-length:# of bytes uploaded thus far
 *
 * on completion, list format is -
 * filename:finished:upload-status
 * filename:finished:upload-status
 */
