# Process this file with autoconf to produce a configure script.
AC_INIT(mfile, VERSION, BUG-REPORT-ADDRESS)
AC_CONFIG_SRCDIR([upload/tpupload.c])
AC_CONFIG_HEADER([config.h])

# Checks for programs.
AC_PROG_CC
AC_PROG_MAKE_SET
AC_PROG_INSTALL

# Checks for libraries.
# FIXME: Replace `main' with a function in `-lcgi':
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

AC_CONFIG_FILES([Makefile
                 libcgi/Makefile
                 upload/Makefile])
AC_OUTPUT
