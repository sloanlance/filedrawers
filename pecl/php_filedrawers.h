#ifndef FILEDRAWERS_H
#define FILEDRAWERS_H			1

#define PHP_FILEDRAWERS_VERSION		"0.2"
#define PHP_FILEDRAWERS_EXTNAME		"filedrawers"

PHP_FUNCTION( filedrawers_rename );
PHP_FUNCTION( filedrawers_unlink );

extern zend_module_entry		filedrawers_module_entry;

#endif /* FILEDRAWERS_H */
