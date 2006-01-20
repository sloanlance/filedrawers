# Process this file with autoconf to produce a configure script.
AC_INIT(filedrawers, 0.5, BUG-REPORT-ADDRESS)
AC_CONFIG_SRCDIR([upload/tpupload.c])

AC_ARG_WITH(project_name,
            AC_HELP_STRING([--with-project_name=project_name],
                           [name of filedrawers root directory]),
            project_name="$withval",
            [project_name="filedrawers"])
AC_SUBST(project_name)

AC_ARG_WITH(shtml_dir_name,
            AC_HELP_STRING([--with-shtml_dir_name=shtml_dir_name],
                           [name of sub-directory to contain ssl html files]),
            shtml_dir_name="$withval",
            [shtml_dir_name="html-ssl"])
AC_SUBST(shtml_dir_name)

AC_ARG_WITH(html_dir_name,
            AC_HELP_STRING([--with-html_dir_name=html_dir_name],
                           [name of sub-directory to contain non-ssl html files]),
            html_dir_name="$withval",
            [html_dir_name="html"])
AC_SUBST(html_dir_name)

AC_ARG_WITH(object_dir_name,
            AC_HELP_STRING([--with-object_dir_name=object_dir_name],
                           [name of sub-directory to contain object files]),
            object_dir_name="$withval",
            [object_dir_name="objects"])
AC_SUBST(object_dir_name)

AC_ARG_WITH(smarty_dir_name,
            AC_HELP_STRING([--with-smarty_dir_name=smarty_dir_name],
                           [name of sub-directory to contain smarty files]),
            smarty_dir-name="$withval",
            [smarty_dir_name="smarty"])
AC_SUBST(smarty_dir_name)

AC_ARG_WITH(cgi_dir_name,
            AC_HELP_STRING([--with-cgi_dir_name=cgi_dir_name],
                           [name of sub-directory to contain cgi binaries]),
            cgi_dir_name="$withval",
            [cgi_dir_name="cgi-ssl"])
AC_SUBST(cgi_dir_name)

AC_ARG_WITH(lib_dir_name,
            AC_HELP_STRING([--with-lib_dir_name=lib_dir_name],
                           [name of sub-directory to contain php libraries]),
            lib_dir_name="$withval",
            [lib_dir_name="lib"])
AC_SUBST(lib_dir_name)

AC_ARG_WITH(js_dir_name,
            AC_HELP_STRING([--with-js_dir_name=js_dir_name],
                           [name of sub-directory to contain javascript]),
            js_dir_name="$withval",
            [js_dir_name="js"])
AC_SUBST(js_dir_name)

AC_ARG_WITH(css_dir_name,
            AC_HELP_STRING([--with-css_dir_name=css_dir_name],
                           [name of sub-directory to contain style sheets]),
            css_dir_name="$withval",
            [css_dir_name="css"])
AC_SUBST(css_dir_name)

AC_ARG_WITH(install_group,
            AC_HELP_STRING([--with-install_group=cgi_dir_name],
                           [name of group to have installed file ownership]),
            install_group="$withval",
            [install_group="staff"])
AC_SUBST(install_group)

AC_ARG_WITH(service_name,
            AC_HELP_STRING([--with-service_name=service_name],
                           [name of the service]),
            service_name="$withval",
            [service_name="mfile.umich.edu"])
AC_SUBST(service_name)

AC_ARG_WITH(service_url,
            AC_HELP_STRING([--with-service_url=service_url],
                           [url for the filedrawers service]),
            service_url="$withval",
            [service_url="http://mfile.umich.edu"])
AC_SUBST(service_url)

AC_ARG_WITH(secure_service_url,
            AC_HELP_STRING([--with-secure_service_url=secure_service_url],
                           [secure url for the filedrawers service]),
            secure_service_url="$withval",
            [secure_service_url="https://mfile.umich.edu"])
AC_SUBST(secure_service_url)

AC_ARG_WITH(error_url,
            AC_HELP_STRING([--with-error_url=error_url],
                           [default URL to redirect back to in case of error]),
            error_url="$withval",
            [error_url="https://mfile.umich.edu/?error=true"])
AC_SUBST(error_url)

AC_ARG_WITH(sdb_host,
            AC_HELP_STRING([--with-sdb_host=sdb_host],
                           [hostname for supportgroups database]),
            sdb_host="$withval",
            [sdb_host=""])
AC_SUBST(sdb_host)

AC_ARG_WITH(sdb_name,
            AC_HELP_STRING([--with-sdb_name=sdb_name],
                           [database name for supportgroups database]),
            sdb_name="$withval",
            [sdb_name=""])
AC_SUBST(sdb_name)

AC_ARG_WITH(sdb_ro_user,
            AC_HELP_STRING([--with-sdb_ro_user=sdb_ro_user],
                           [username for supportgroups database]),
            sdb_ro_user="$withval",
            [sdb_ro_user=""])
AC_SUBST(sdb_ro_user)

AC_ARG_WITH(sdb_ro_password,
            AC_HELP_STRING([--with-sdb_ro_password=sdb_ro_password],
                           [read-only password for supportgroups database]),
            sdb_ro_password="$withval",
            [sdb_ro_password=""])
AC_SUBST(sdb_ro_password)

AC_ARG_ENABLE(allow_support_logging, AC_HELP_STRING([--enable-allow_support_logging], [enables the creation of logs for allow-support]), allow_support_logging="yes", allow_support_logging="" )
AC_SUBST(allow_support_logging)

# Checks for programs.
AC_PROG_CC
AC_PROG_INSTALL
AC_PROG_MAKE_SET

# Checks for libraries.
AC_CHECK_LIB([cgi], [cf_free])

# Checks for header files.
AC_HEADER_STDC
AC_CHECK_HEADERS([fcntl.h limits.h stdlib.h string.h sys/param.h unistd.h])

# Checks for typedefs, structures, and compiler characteristics.
AC_TYPE_OFF_T

# Checks for library functions.
AC_FUNC_MALLOC
AC_FUNC_MEMCMP
AC_CHECK_FUNCS([memchr memmove memset strcasecmp strdup strerror strncasecmp strstr strtol])

AC_CONFIG_SUBDIRS(libcgi)

AC_CONFIG_FILES(Makefile)

AC_OUTPUT
