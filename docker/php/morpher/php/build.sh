#!/bin/bash

cd ../cpp-static-lib/linux
make
cd ../../php

set -e

phpize
./configure --enable-hello CXXFLAGS="-O2 -std=c++11" CFLAGS="-O2" LDFLAGS="-static"
make clean
make
cd modules
g++ -shared -o morpher.so -Wl,-L. -Wl,--whole-archive -l:hello.a -l:../../cpp-static-lib/linux/libmorpher.a -Wl,--no-whole-archive
