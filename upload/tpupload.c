/*
 * Copyright (c) 2002 Regents of The University of Michigan.
 * All Rights Reserved.  See COPYRIGHT.
 */

#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <sys/param.h>
#include "libcgi/cgi.h"

#define SESSION_PATH	"/tmp/"		/* write progress here */
#define VALID_PATH	"/afs/"		/* needs to begin with afs */

int func_initialize( char **, struct cgi_list * );
int func_progress( char *, int );
void progress_complete();


struct cgi_list cl[] = {
#define CL_SESSIONID		0
    { "sessionid", CGI_TYPE_STRING, NULL },
#define CL_PATH			1	
    { "path", CGI_TYPE_STRING, NULL },
#define CL_RETURNTOURI		2
    { "returnToURI", CGI_TYPE_STRING, NULL },
#define CL_FILE			3	
    { "file", CGI_TYPE_FILE, NULL },
#define CL_CLOBBER		4	
    { "overwrite_file", CGI_TYPE_STRING, NULL },
    { NULL, CGI_TYPE_UNDEF, NULL },
};


struct status {
    FILE		*s_fp;	
    char		*s_path;
    char		*s_sessionid;
    char		*s_returnuri;
    long int		s_content_len;
    long int		s_bytes_uploaded;
    long int		s_total_bytes_uploaded;
};


struct status 		*st;
static char     	*ref_error = _URL_ERROR_PAGE;
char            	*user = NULL;


    int
func_initialize( char **dir, struct cgi_list *cl )
{
    int			i;


    for ( i = 0; cl[i].cl_key != NULL; i++ ) {

	if ( strcmp( cl[i].cl_key, "returnToURI") == 0 ) {
	    if ( st->s_returnuri != NULL ) {
	         continue;
	    }
	    if (( st->s_returnuri = malloc( strlen( cl[i].cl_data ) + 1 )) 
						    == NULL ) {
		perror( "malloc" );
		return( -1 );
	    }
	    strcpy( st->s_returnuri, cl[i].cl_data );
	    fprintf( stderr, "func_init: RETURN URI = %s\n", st->s_returnuri );
	}

	if ( strcmp( cl[i].cl_key, "sessionid") == 0 ) {
	    if ( st->s_sessionid != NULL ) {
		continue;
	    }
	    st->s_content_len = atoi( getenv("CONTENT_LENGTH"));

	    if ( cl[i].cl_data == NULL ) {
		fprintf( stderr, "func_init: no session id\n" );
		return( -1 );
	    }

	    if (( st->s_sessionid = malloc(strlen(SESSION_PATH) + 
		    strlen(cl[i].cl_data ) + 1 )) == NULL ) {
		perror( "malloc" );
		return( -1 );
	    }
	    strcpy( st->s_sessionid, SESSION_PATH );
	    strcat( st->s_sessionid, cl[i].cl_data );
	    fprintf( stderr, "func_init: SESSIONID = %s\n", st->s_sessionid );
	    /* open the progess file for writing */
	    if ( st->s_fp == NULL ) {
		if (( st->s_fp = fopen( st->s_sessionid, "w" )) < 0 ) {
		    perror( "open" );
		    return( -1 );
		}
		st->s_total_bytes_uploaded = 0;
	    }
	    continue;
	}

	if ( strcmp( cl[i].cl_key, "path" ) == 0 ) {
	    /* need to make sure that it begins with afs */
	    if ( st->s_path != NULL ) {
		continue;
	    }
	    if ( cl[i].cl_data == NULL ) {
		fprintf( stderr, "func_init: no path\n" );
		return( -1 );
	    }
	    if ( strncmp( VALID_PATH, cl[i].cl_data, 5 ) == 0 ) {
		if (( st->s_path = malloc( strlen( cl[i].cl_data )
							+ 1 )) == NULL) {
		    perror( "malloc" );
		    return( -1 );
		}
		strcpy( st->s_path, cl[i].cl_data );
		*dir = malloc(strlen(st->s_path) + 1 );
		strcpy( *dir, st->s_path );
		fprintf( stderr, "func_init: PATH = %s\n", st->s_path );
	    } else {
		fprintf( stderr, "func_init: invalid path to upload into\n" );
		return( -1 );
	    }
	    continue;
	}

	if ( strcmp( cl[i].cl_key, "file" ) == 0 ) {
	    fprintf( stderr, "func_init - key is file\n" );
	    continue;
	}

    } /* for */
    return( 0 );
}

    int
func_progress( char *filename, int bytes_uploaded )
{
    char	buf[ 1024 ];

    if (( st->s_sessionid == NULL ) || ( st->s_path == NULL )) {
	fprintf( stderr, "error: no progress file or no upload path set\n" );
	return( -1 );
    }

    st->s_total_bytes_uploaded += bytes_uploaded;

    fseek( st->s_fp, 0, SEEK_SET );
    snprintf( buf, sizeof(buf), "%s:%ld:%ld:\n", filename, st->s_content_len,
						st->s_total_bytes_uploaded );
    fprintf( st->s_fp, "%-1024s", buf );
    fflush( st->s_fp );

    return(0);
}


/* free the status struct */
st_free( )
{
    if ( st != NULL ) {
	if ( st->s_sessionid != NULL ) {
	    free( st->s_sessionid );
	}
	if ( st->s_path != NULL ) {
	    free( st->s_path );
	}
	if ( st->s_returnuri != NULL ) {
	    free( st->s_returnuri );
	}
	free( st );
    }
}

    void
progress_complete()
{
    int	i;
    struct cgi_file	*ptr;


    truncate( st->s_sessionid, (off_t)0 );
    fseek( st->s_fp, 0, SEEK_SET );
    for( i = 0; cl[i].cl_key != NULL; i++ ) {
	if ( cl[i].cl_type != CGI_TYPE_FILE ) {
	    continue;
	}
	if ( cl[i].cl_data == NULL ) {
	    continue;
	}
	fseek( st->s_fp, 0, SEEK_SET );
	truncate( st->s_sessionid, (off_t)0 );
	for( ptr = cl[i].cl_data; ptr != NULL; ptr = ptr->cf_next ) {
	fprintf( st->s_fp, "%s:finished:%s\n", 
				    ptr->cf_name, ptr->cf_status );
	}
	fflush( st->s_fp );
    }
    return;
}


main()
{
    CGIHANDLE		*cgi;
    char        	*agent_string = "did not get browser string";
    struct function	func;
    char		*ref, *ptr_ref;


    func.f_init = func_initialize;
    func.f_progress = func_progress;

    if (( agent_string = (char *)getenv( "HTTP_USER_AGENT" )) != NULL ) {
	fprintf( stderr, "Using browser %s\n\n", agent_string );
    }

    if (( user = (char *)getenv( "REMOTE_USER" )) == NULL ) {
	fprintf( stderr, "ERROR:  did not get user name.\n" );
	printf( "Location: %s\n\n", ref_error ); 
    }

    fprintf( stderr, "cgi_init: getting a cgi handle.\n" );
    if (( cgi = cgi_init()) == NULL ) {
	fprintf( stderr, "ERROR:  Failed to create buffer\n" );
	printf( "Location: %s\n\n", ref_error ); 
    }

    if (( st = ( struct status *) malloc( sizeof( struct status ))) == NULL ) {
	fprintf( stderr, "ERROR: malloc for st failed\n" );
	cgi_free( cgi );
	printf( "Location: %s\n\n", ref_error ); 
    }
    st->s_fp = NULL;
    st->s_sessionid = NULL;
    st->s_path = NULL;
    st->s_returnuri = NULL;

    fprintf( stderr, "cgi_multipart: about to do a cgi_multipart.\n" );
    if (( cgi_multipart( cgi, cl, "/tmp", &func )) != 0 ) {
	fprintf( stderr, "ERROR: cgi_multipart failed\n" );
	    /* but did we upload anything ? */
    } else {
	fprintf( stderr, "tpupload: cgi_multipart successful \n");
    }
    if ( st->s_fp != NULL ) {
	progress_complete( );
	fclose( st->s_fp );
    }

    if ( st->s_returnuri == NULL ) {
	printf( "Location: %s\n\n", ref_error );
        fprintf( stderr, "Redirect to: %s\n", ref_error );
    } else {
        printf( "Location: %s\n\n", st->s_returnuri );
        fprintf( stderr, "redirect to: %s\n", st->s_returnuri );
    }

    cgi_cl_free( cl );
    cgi_free( cgi );
    st_free();
    fprintf( stderr, "exit tpupload\n");
    exit( 0 );
}


/* Format of progress (sessionid) file				*/
/*								*/
/* during upload there is one line, format is -			*/	
/* filename:content-length:# of bytes uploaded thus far		*/
/*								*/
/* on completion, list format is -				*/
/* filename:finished:upload-status				*/
/* filename:finished:upload-status				*/

