# Process this file with autoconf to produce a configure script.
AC_INIT(tpupload, VERSION, BUG-REPORT-ADDRESS)
AC_CONFIG_SRCDIR([tpupload.c])
#AC_CONFIG_HEADER([config.h])

AC_ARG_WITH(error_url, AC_HELP_STRING([--with-error_url=error_url], [default URL to redirect back to in case of error]), error_url="$withval", error_url="https://mfile.umich.edu/?error=true")
AC_SUBST(error_url)

# Checks for programs.
AC_PROG_CC

# Checks for libraries.
# FIXME: Replace `main' with a function in `-lcgi':
AC_CHECK_LIB([cgi], [cgi_free])

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
