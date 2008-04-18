AC_DEFUN([FILEDRAWERS_CHECK_PHPIZE],
[
    AC_MSG_CHECKING(for phpize)
    phpize_path="phpize"
    AC_ARG_WITH(phpize, AC_HELP_STRING([--with-phpize=PATH], [path to phpize]),
		phpize_path="$withval", phpize_path="/usr/bin/phpize")
    if test -f "$phpize_path"; then
	found_phpize="yes"
    fi
    if test x_$found_phpize != x_yes; then
	AC_MSG_ERROR(cannot find phpize)
    else
	phpize="$phpize_path"
    fi
    AC_MSG_RESULT(yes)
])

AC_DEFUN([FILEDRAWERS_CONFIG_MODULE],
[
    AC_MSG_NOTICE([Configuring filedrawers PECL module...])
    (cd pecl; "$phpize"; ./configure)
])

AC_DEFUN([FILEDRAWERS_CHECK_MYSQL],
[
    AC_MSG_CHECKING(for mysql)
    mysqldirs="/usr /usr/mysql /usr/local /usr/local/mysql"
    AC_ARG_WITH(mysql,
	    AC_HELP_STRING([--with-mysql=DIR], [path to mysql]),
	    mysqldirs="$withval")
    for dir in $mysqldirs; do
	mysqldir="$dir"
	if test -f "$dir/bin/mysql_config"; then
	    found_mysql=yes
	    break
	fi
    done
    if test x_$found_mysql != x_yes; then
	AC_MSG_ERROR(cannot find mysql)
    fi

    mysql_cflags="`$mysqldir/bin/mysql_config --cflags`"
    mysql_libs="`$mysqldir/bin/mysql_config --libs`"
    AC_MSG_RESULT(yes)
])
