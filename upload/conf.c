#include <sys/types.h>
#include <sys/param.h>
#include <ctype.h>
#include <errno.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>

#include "conf.h"

struct fdconf {
    char		*fd_key;
    char		*fd_val;
    struct fdconf	*fd_next;
};

struct fdconf		*fdcfg = NULL;

void print_config( void );

/* simple split of line into two args */
    int
splitline( char *line, char ***lineav )
{
    static char		**lav = NULL;

    if ( lav == NULL ) {
	if (( lav = ( char ** )malloc( 2 * sizeof( char * ))) == NULL ) {
	    perror( "malloc" );
	    exit( 2 );
	}
    }

    while ( isspace( *line )) line++;
    if ( *line == '\0' ) {
	/* blank line */
	lav = NULL;
	return( 0 );
    }

    lav[ 0 ] = line;

    while ( !isspace( *line )) line++;
    *line++ = '\0';
    while ( isspace( *line )) line++;
    if ( *line == '\0' ) {
	return( -1 );
    }
    
    lav[ 1 ] = line;
    *lineav = lav;

    return( 2 );
}

    int
filedrawers_config_read( char *path )
{
    struct fdconf	*new, **cur;
    FILE		*conf;
    char		line[ MAXPATHLEN ];
    char		**lineav;
    int			linenum = 0;
    int			rc, len;

    if (( conf = fopen( path, "r" )) == NULL ) {
	fprintf( stderr, "fopen %s: %s\n", path, strerror( errno ));
	return( -1 );
    }

    while ( fgets( line, sizeof( line ), conf ) != NULL ) {
	linenum++;

	len = strlen( line );
	if ( line[ len - 1 ] != '\n' ) {
	    fprintf( stderr, "%s: line %d: line too long\n", path, linenum );
	    return( -1 );
	}
	line[ len - 1 ] = '\0';

	/* skip blanks and comments */
	if ( *line == '\0' || *line == '#' ) {
	    continue;
	}

	if (( rc = splitline( line, &lineav )) < 0 ) {
	    fprintf( stderr, "%s: line %d: two arguments required\n",
			path, linenum );
	    return( -1 );
	}

	if ( rc == 0 || *lineav[ 0 ] == '#' ) {
	    continue;
	}

	if (( new = ( struct fdconf * )malloc(
			sizeof( struct fdconf ))) == NULL ) {
	    perror( "malloc" );
	    exit( 2 );
	}
	if (( new->fd_key = strdup( lineav[ 0 ] )) == NULL ) {
	    perror( "strdup" );
	    exit( 2 );
	}
	if (( new->fd_val = strdup( lineav[ 1 ] )) == NULL ) {
	    perror( "strdup" );
	    exit( 2 );
	}

	for ( cur = &fdcfg; *cur != NULL; cur = &( *cur )->fd_next )
	    ;

	new->fd_next = *cur;
	*cur = new;
    }

    if ( fclose( conf ) != 0 ) {
	fprintf( stderr, "fclose %s: %s\n", path, strerror( errno ));
	exit( 2 );
    }

    return( 0 );
}

    char *
filedrawers_config_value_for_key( char *key )
{
    struct fdconf		*cur;

    for ( cur = fdcfg; cur != NULL; cur = cur->fd_next ) {
	if ( strcmp( key, cur->fd_key ) == 0 ) {
	    return( cur->fd_val );
	}
    }

    return( NULL );
}

    void
print_config( void )
{
    struct fdconf		*cur;

    for ( cur = fdcfg; cur != NULL; cur = cur->fd_next ) {
	printf( "key: %s\tval: %s\n", cur->fd_key, cur->fd_val );
    }
}
