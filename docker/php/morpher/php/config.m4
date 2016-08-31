PHP_ARG_ENABLE(hello, whether to enable Hello
World support,
[ --enable-hello   Enable Hello World support])
if test "$PHP_HELLO" = "yes"; then
    PHP_REQUIRE_CXX()
    PHP_SUBST(HELLO_SHARED_LIBADD)
    PHP_ADD_LIBRARY(stdc++, 1, HELLO_SHARED_LIBADD)
	AC_DEFINE(HAVE_HELLO, 1, [Whether you have Hello World])
	PHP_NEW_EXTENSION(hello, bsdua/utf8.c hello.cpp, $ext_shared, , -D_UNICODE)
fi
