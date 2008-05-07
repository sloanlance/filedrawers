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
#include <mysql.h>

#include "conf.h"

#ifndef AFS_VALID_PATH
# define AFS_VALID_PATH		"/afs/"
#endif /* AFS_VALID_PATH */

int		upload_progress_db_insert( char *, char *,
					   long long, long long );
int		upload_progress_db_update( char *, char *, long long, char );
int		upload_init( char **, struct cgi_list * );
int		upload_progress( char *, int );

extern int	errno;

static MYSQL	*mysql = NULL;
static MYSQL_STMT	*stmt = NULL;
char		*progress_host;
char		*progress_login;
char		*progress_passwd;
char		*progress_db;
char		*progress_table;

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
    char		*s_sid;
    char		*s_uri;
    long long		s_content_len;
    long long		s_total_bytes_uploaded;
};

struct status 		*st;
char     		*ref_error;
char            	*user = NULL;
char			*fdtmpdir;
char			*validpath;
int			init_done = 0;
int			db_update = 0;

#define BIND_PARAMS( a, b, c, d, e ) \
{ \
    (a).buffer_type = (b); \
    (a).buffer = (void *)(c); \
    (a).length = (d); \
    (a).is_null = (my_bool *)(e); \
}

/* #undef FILEDRAWERS_UPLOAD_CGI_DEBUG */
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
upload_progress_db_insert( char *sid, char *fname, long long sz, long long rcvd)
{
    MYSQL_BIND		bind[ 5 ];
    my_bool		is_null = 0;
    static char		sql[ MAXPATHLEN ];
    long		flen, sidlen, szlen, rlen, dlen;
    char		done;
    int			len;

    if ( stmt == NULL ) {
	if (( stmt = mysql_stmt_init( mysql )) == NULL ) {
	    fprintf( stderr, "mysql_stmt_init: %s\n", mysql_error( mysql ));
	    return( -1 );
	}

	if (( len = snprintf( sql, sizeof( sql ), "INSERT INTO %s "
		"(session_id, filename, size, received, complete) "
		"VALUES (?, ?, ?, ?, ?)", progress_table )) >= sizeof( sql )) {
	    fprintf( stderr, "sql INSERT failed: statement too long\n" );
	    return( -1 );
	}

	if ( mysql_stmt_prepare( stmt, sql, len ) != 0 ) {
	    fprintf( stderr, "mysql_stmt_prepare INSERT failed: %s\n",
			mysql_stmt_error( stmt ));
	    return( -1 );
	}
    }

    memset( bind, 0, sizeof( bind ));

    BIND_PARAMS( bind[ 0 ], MYSQL_TYPE_STRING, sid, &sidlen, &is_null );
    BIND_PARAMS( bind[ 1 ], MYSQL_TYPE_STRING, fname, &flen, &is_null );
    BIND_PARAMS( bind[ 2 ], MYSQL_TYPE_LONGLONG, &sz, &szlen, &is_null );
    BIND_PARAMS( bind[ 3 ], MYSQL_TYPE_LONGLONG, &rcvd, &rlen, &is_null );
    BIND_PARAMS( bind[ 4 ], MYSQL_TYPE_TINY, &done, &dlen, &is_null );

    if ( mysql_stmt_bind_param( stmt, bind ) != 0 ) {
	fprintf( stderr, "mysql_stmt_bind_param failed: %s\n",
		mysql_stmt_error( stmt ));
	return( -1 );
    }

    flen = strlen( fname );
    sidlen = strlen( sid );
    done = 0;

    if ( mysql_stmt_execute( stmt ) != 0 ) {
	fprintf( stderr, "mysql_stmt_execute failed: %s\n",
		mysql_stmt_error( stmt ));
	return( -1 );
    }

    return( 0 );
}

    int
upload_progress_db_update( char *fname, char *sid, long long rcvd, char done )
{
    MYSQL_BIND		bind[ 4 ];
    my_bool		is_null = 0;
    static char		sql[ MAXPATHLEN ];
    long		flen, sidlen, rlen, dlen;
    int			len;

    if ( stmt == NULL ) {
	if (( stmt = mysql_stmt_init( mysql )) == NULL ) {
	    fprintf( stderr, "mysql_stmt_init: %s\n", mysql_error( mysql ));
	    return( -1 );
	}

	if (( len = snprintf( sql, sizeof( sql ), "UPDATE %s SET "
		"filename=?,received=?,complete=? WHERE session_id=?",
		progress_table )) >= sizeof( sql )) {
	    fprintf( stderr, "sql UPDATE failed: statement too long\n" );
	    return( -1 );
	}

	if ( mysql_stmt_prepare( stmt, sql, len ) != 0 ) {
	    fprintf( stderr, "mysql_stmt_prepare UPDATE failed: %s\n",
			mysql_stmt_error( stmt ));
	    return( -1 );
	}
    }

    memset( bind, 0, sizeof( bind ));

    BIND_PARAMS( bind[ 0 ], MYSQL_TYPE_STRING, fname, &flen, &is_null );
    BIND_PARAMS( bind[ 1 ], MYSQL_TYPE_LONGLONG, &rcvd, &rlen, &is_null );
    BIND_PARAMS( bind[ 2 ], MYSQL_TYPE_TINY, &done, &dlen, &is_null );
    BIND_PARAMS( bind[ 3 ], MYSQL_TYPE_STRING, sid, &sidlen, &is_null );

    if ( mysql_stmt_bind_param( stmt, bind ) != 0 ) {
	fprintf( stderr, "mysql_stmt_bind_param failed: %s\n",
		mysql_stmt_error( stmt ));
	return( -1 );
    }

    flen = strlen( fname );
    sidlen = strlen( sid );

    if ( mysql_stmt_execute( stmt ) != 0 ) {
	fprintf( stderr, "mysql_stmt_execute UPDATE failed: %s\n",
		mysql_stmt_error( stmt ));
	return( -1 );
    }

    return( 0 );
}

    int
upload_init( char **dir, struct cgi_list *cl )
{
    struct stat		s;
    dev_t		vdev;
    char		*p;
    char		*filename = "upload initializing";
    extern int          cgi_file_clobber;

    if ( cl[ CL_RETURNURI ].cl_data == NULL ||
		cl[ CL_SESSIONID ].cl_data == NULL ||
		cl[ CL_PATH ].cl_data == NULL ) {
	fprintf( stderr, "upload_init: missing required data from POST\n" );
	return( -1 );
    }

    if (( st->s_uri = strdup( cl[ CL_RETURNURI ].cl_data )) == NULL ) {
	perror( "strdup" );
	return( -1 );
    }
    debug( "upload_init: RETURN_URI = %s\n", st->s_uri );

    /*
     * CONTENT_LENGTH includes the post headers, meaning
     * s_total_bytes_uploaded will never be equal to
     * s_content_len. close enough for gov't work, tho.
     */
    errno = 0;
    st->s_content_len = strtoll( getenv( "CONTENT_LENGTH" ), NULL, 10 );
    if ( errno ) {
	fprintf( stderr, "strtoll %s failed: %s\n",
		 getenv( "CONTENT_LENGTH" ), strerror( errno ));
	return( -1 );
    }

    /* no special path characters allowed in the session ID */
    for ( p = cl[ CL_SESSIONID ].cl_data; *p != '\0'; p++ ) {
	if ( !isalnum( *p )) {
	    fprintf( stderr, "%s: invalid session ID\n",
		    ( char * )cl[ CL_SESSIONID ].cl_data );
	    return( -1 );
	}
    }
    if (( st->s_sid = strdup((char *)cl[ CL_SESSIONID ].cl_data )) == NULL ) {
	perror( "strdup" );
	return( -1 );
    }
    st->s_total_bytes_uploaded = 0;
    debug( "upload_init: SESSIONID = %s\n", st->s_sid );

    /*
     * data here is the directory where the file will be uploaded.
     * libcgi appends the filename itself.
     */
    if ( strlen( cl[ CL_PATH ].cl_data ) >= MAXPATHLEN ) {
	fprintf( stderr, "%s: too long\n", (char *)cl[ CL_PATH ].cl_data );
	return( -1 );
    }

    /* libcgi uses dir as the upload destination directory */
    if (( *dir = strdup(( char * )cl[ CL_PATH ].cl_data )) == NULL ) {
	perror( "strdup" );
	return( -1 );
    }

    /* Ensure destination dir device matches validpath's */
    if ( stat( validpath, &s ) != 0 ) {
	fprintf( stderr, "stat %s: %s\n", validpath, strerror( errno ));
	return( -1 );
    }
    vdev = s.st_dev;
    if ( stat( *dir, &s ) != 0 ) {
	fprintf( stderr, "stat %s: %s\n", *dir, strerror( errno ));
	return( -1 );
    }
    if ( s.st_dev != vdev ) {
	fprintf( stderr, "%s: prohibited upload destination\n", *dir );
	return( -1 );
    }
    debug( "upload_init: UPLOAD_DIR = %s\n", *dir );

    /*
     * CL_FILE data is a list of files that will be uploaded. create
     * an entry in the progress table for the first one, then
     * update the filename on each call to upload_progress().
     */
    if ( !init_done && mysql ) {
	if ( upload_progress_db_insert( cl[ CL_SESSIONID ].cl_data,
		    filename, st->s_content_len, 0 ) != 0 ) {
	    return( -1 );
	}
	if ( stmt != NULL ) {
	    if ( mysql_stmt_close( stmt ) != 0 ) {
		fprintf( stderr, "upload_init: mysql_stmt_close failed: %s\n",
			mysql_stmt_error( stmt ));
		return( -1 );
	    }
	    stmt = NULL;
	}
	init_done = 1;
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
    if ( mysql == NULL ) {
	return( 0 );
    }

    st->s_total_bytes_uploaded += bytes_uploaded;

    if (( db_update = !db_update ) == 0 ) {
	/* give the progress feedback server a break. */
	return( 0 );
    }

    if ( upload_progress_db_update( filename, st->s_sid,
		st->s_total_bytes_uploaded, 0 ) != 0 ) {
	return( -1 );
    }

    return( 0 );
}

    static void
upload_complete( int rc )
{
    struct cgi_file	*cf;
    char		*buf;

    if ( mysql == NULL ) {
	return;
    }

    if ( rc != 0 ) {
	/*
	 * update progress db with error message so
	 * PHP frontend can detect and display it.
	 */

	for ( cf = cl[ CL_FILE ].cl_data; cf != NULL; cf = cf->cf_next ) {
	    /* a non-NULL cf_status holds the error message. */
	    if ( cf->cf_status != NULL ) {
		break;
	    }
	}

	if ( cf != NULL && cf->cf_status != NULL ) {
	    if (( buf = (char *)malloc( strlen( "ERROR: " ) +
				strlen( cf->cf_status ) + 1 )) == NULL ) {
		perror( "upload_complete: malloc" );
		return;
	    }
	    strcpy( buf, "ERROR: " );
	    strcat( buf, cf->cf_status );
	} else {
	    if (( buf = strdup( "ERROR: internal error" )) == NULL ) {
		perror( "upload_complete: strdup" );
		return;
	    }
	}

	upload_progress_db_update( buf, st->s_sid, -1, 1 );
    } else {
	upload_progress_db_update( "upload complete", st->s_sid,
		st->s_total_bytes_uploaded, 1 );
    }
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
read_conf( void )
{
    ref_error = _URL_ERROR_PAGE;

    if ( filedrawers_config_read( _FILEDRAWERS_CONFIG_PATH ) != 0 ) {
	fprintf( stderr, "ERROR: read %s failed\n", _FILEDRAWERS_CONFIG_PATH );
	goto read_conf_failed;
    }

    if (( validpath = filedrawers_config_value_for_key(
				FILEDRAWERS_KEY_VALIDPATH )) == NULL ) {
	validpath = AFS_VALID_PATH;
    }
    if (( ref_error = filedrawers_config_value_for_key(
				FILEDRAWERS_KEY_ERRORURL )) == NULL ) {
	ref_error = _URL_ERROR_PAGE;
    }

    if (( progress_host = filedrawers_config_value_for_key(
				FILEDRAWERS_KEY_PROGRESSDBHOST )) == NULL ) {
	fprintf( stderr, "ERROR: conf missing %s\n",
				FILEDRAWERS_KEY_PROGRESSDBHOST );
	goto read_conf_failed;
    }
    if (( progress_login = filedrawers_config_value_for_key(
				FILEDRAWERS_KEY_PROGRESSDBLOGIN )) == NULL ) {
	fprintf( stderr, "ERROR: conf missing %s\n",
				FILEDRAWERS_KEY_PROGRESSDBLOGIN );
	goto read_conf_failed;
    }
    if (( progress_passwd = filedrawers_config_value_for_key(
				FILEDRAWERS_KEY_PROGRESSDBPASSWD )) == NULL ) {
	fprintf( stderr, "ERROR: conf missing %s\n",
				FILEDRAWERS_KEY_PROGRESSDBPASSWD );
	goto read_conf_failed;
    }
    if (( progress_db = filedrawers_config_value_for_key(
				FILEDRAWERS_KEY_PROGRESSDBNAME )) == NULL ) {
	fprintf( stderr, "ERROR: conf missing %s\n",
				FILEDRAWERS_KEY_PROGRESSDBNAME );
	goto read_conf_failed;
    }
    if (( progress_table = filedrawers_config_value_for_key(
				FILEDRAWERS_KEY_PROGRESSDBTABLE )) == NULL ) {
	progress_table = "filedrawers_progress";
    }
			
    return;

read_conf_failed:
    printf( "Location: %s\n\n", ref_error ); 
    exit( 0 );
}

    int
main( int ac, char *av[] )
{
    CGIHANDLE		*cgi;
    struct function	func;
    char        	*agent_string = "did not get browser string";
    char		*dir = NULL;
    char		*prog;
    int			rc;

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

    read_conf();

    if (( user = (char *)getenv( "REMOTE_USER" )) == NULL ) {
	fprintf( stderr, "ERROR: %s did not get user name.\n", prog );
	printf( "Location: %s\n\n", ref_error ); 
	exit( 2 );
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
    st->s_sid = NULL;
    st->s_uri = NULL;

    if (( mysql = mysql_init( NULL )) == NULL ) {
	fprintf( stderr, "mysql_init failed, progress feedback disabled." );
    } else if ( mysql_real_connect( mysql, progress_host, progress_login,
		    progress_passwd, progress_db, 3306, NULL, 0 ) == 0 ) {
	fprintf( stderr, "mysql_real_connect: %s", mysql_error( mysql ));
	exit( 2 );
    }

    debug( "%s: calling cgi_multipart\n", prog );
    if (( rc = cgi_multipart( cgi, cl, dir, &func )) != 0 ) {
	fprintf( stderr, "ERROR: cgi_multipart failed\n" );
	printf( "Location: %s\n\n", ref_error );
    } else {
	debug( "%s: cgi_multipart successful\n", prog );
    }

    if ( st->s_uri == NULL ) {
	printf( "Location: %s\n\n", ref_error );
        debug( "redirect to: %s\n", ref_error );
    } else {
	upload_complete( rc );
        printf( "Location: %s\n\n", st->s_uri );
        debug( "redirect to: %s\n", st->s_uri );
    }

    if ( mysql ) {
	if ( stmt ) {
	    ( void )mysql_stmt_close( stmt );
	}
	mysql_close( mysql );
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
