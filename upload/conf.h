#ifndef _FILEDRAWERS_CONFIG_PATH
#define _FILEDRAWERS_CONFIG_PATH		"./filedrawers.conf"
#endif /* _FILEDRAWERS_CONFIG_PATH */

#define FILEDRAWERS_KEY_ERRORURL		"errorurl"
#define FILEDRAWERS_KEY_PROGRESSDBHOST		"progressdbhost"
#define FILEDRAWERS_KEY_PROGRESSDBLOGIN		"progressdblogin"
#define FILEDRAWERS_KEY_PROGRESSDBPASSWD	"progressdbpassword"
#define FILEDRAWERS_KEY_PROGRESSDBNAME		"progressdbname"
#define FILEDRAWERS_KEY_PROGRESSDBTABLE		"progressdbtable"
#define FILEDRAWERS_KEY_VALIDPATH		"validpath"

int		splitline( char *, char *** );
int		filedrawers_config_read( char * );
char		*filedrawers_config_value_for_key( char * );
void		print_config( void );
