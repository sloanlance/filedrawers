PHP_ARG_ENABLE(filedrawers, whether to enable filedrawers extension,
		[ --enable-filedrawers	Enable filedrawers extension ],
		yes)

if test "$PHP_FILEDRAWERS" != no; then
    AC_DEFINE(HAVE_FILEDRAWERS, 1, [filedrawers support])
    PHP_NEW_EXTENSION(filedrawers, filedrawers.c, $ext_shared)
fi
