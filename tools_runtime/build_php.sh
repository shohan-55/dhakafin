#!/usr/bin/env bash
# Rebuild PHP 8.5.10 CLI from sources fetched via public GitHub (no auth needed).
# Produces /home/user/php and packs it to tools_runtime/php-dist.tar.gz
set -euo pipefail

REPO=/home/user/dhakafin
export LP=/home/user/php-libs
export SRC=/home/user/build-src
mkdir -p "$LP" "$SRC" "$LP/lib" "$LP/include" "$LP/lib/pkgconfig" "$LP/bin"
export PATH="$LP/bin:$PATH"
export PKG_CONFIG_PATH="$LP/lib/pkgconfig"
cd "$SRC"

echo "[STAGE 1/7] Downloading sources (git protocol, no auth)"
PHPV=8.5.10
if [ ! -f "php-$PHPV.tar.gz" ]; then
  rm -rf distrepo
  git clone --filter=blob:none --no-checkout --depth 1 https://github.com/php/web-php-distributions.git distrepo
  cd distrepo
  git sparse-checkout init --no-cone
  git sparse-checkout set "php-$PHPV.tar.gz"
  git checkout
  cd ..
  mv "distrepo/php-$PHPV.tar.gz" .
  rm -rf distrepo
fi
[ -d "php-$PHPV" ] || tar xzf "php-$PHPV.tar.gz"
echo "php ready"

latest_tag() { git ls-remote --tags "$1" "$2" | grep -oE '[^/]+$' | sort -V | tail -1; }

ZTAG=v1.3.2
[ -d zlib-src ] || { curl -sfL "https://codeload.github.com/madler/zlib/tar.gz/refs/tags/$ZTAG" | tar xz && mv zlib-* zlib-src; }
ONITAG=v6.9.10
[ -d onig-src ] || { curl -sfL "https://codeload.github.com/kkos/oniguruma/tar.gz/refs/tags/$ONITAG" | tar xz && mv oniguruma-* onig-src; }
OSSLTAG=openssl-3.6.4
echo "openssl tag: $OSSLTAG"
[ -d openssl-src ] || { curl -sfL "https://codeload.github.com/openssl/openssl/tar.gz/refs/tags/$OSSLTAG" | tar xz && mv openssl-* openssl-src; }

if [ ! -d sqlite ]; then
  mkdir -p sqlite && cd sqlite
  npm pack better-sqlite3@latest --silent 2>/dev/null || npm pack better-sqlite3 --silent
  tar xzf better-sqlite3-*.tgz package/deps/sqlite3/sqlite3.c package/deps/sqlite3/sqlite3.h 2>/dev/null || tar xzf better-sqlite3-*.tgz
  cd ..
fi
echo "sources ready"

mkpc() {
  cat > "$LP/lib/pkgconfig/$1.pc" <<EOF
prefix=$LP
exec_prefix=\${prefix}
libdir=\${prefix}/lib
includedir=\${prefix}/include

Name: $1
Description: $5
Version: $2
Cflags: $3
Libs: $4
EOF
}

echo "[STAGE 2/7] pkg-config shim"
cat > "$LP/bin/pkg-config" <<'SH'
#!/usr/bin/env bash
for a in "$@"; do
  case "$a" in
    --version) echo "0.29.2"; exit 0;;
    --atleast*) exit 0;;
  esac
done
mods=""; modes=""
for a in "$@"; do
  case "$a" in
    --exists|--modversion|--cflags|--libs|--print-errors|--silence-errors|--static) modes="$modes $a";;
    -*) ;;
    *) case "$a" in *">"*|*"<"*|*"="*) ;; *) mods="$mods $a";; esac;;
  esac
done
dir="${PKG_CONFIG_PATH:-/home/user/php-libs/lib/pkgconfig}"
expand(){ sed -e "s|\${prefix}|/home/user/php-libs|g" -e "s|\${exec_prefix}|/home/user/php-libs|g" -e "s|\${includedir}|/home/user/php-libs/include|g" -e "s|\${libdir}|/home/user/php-libs/lib|g"; }
rc=0; outc=""; outl=""; outv=""
for m in $mods; do
  f="$dir/$m.pc"
  if [ ! -f "$f" ]; then rc=1; continue; fi
  get(){ grep -E "^$2:" "$f" | head -1 | sed "s/^$2:[[:space:]]*//" | expand; }
  case " $modes " in *" --modversion "*) outv="$outv $(grep -E '^Version:' "$f" | head -1 | sed 's/^Version:[[:space:]]*//')";; esac
  case " $modes " in *" --cflags "*) outc="$outc $(get "$f" Cflags)";; esac
  case " $modes " in *" --libs "*) outl="$outl $(get "$f" Libs)";; esac
done
case " $modes " in *" --exists "*) exit $rc;; esac
echo "$outv$outc$outl" | tr ' ' '\n' | grep -v '^$' | sort -u | tr '\n' ' '; echo
exit $rc
SH
chmod +x "$LP/bin/pkg-config"

echo "[STAGE 3/7] zlib"
if [ ! -f "$LP/lib/libz.a" ]; then
  cd "$SRC/zlib-src" && ./configure --prefix="$LP" --static >/dev/null && make -j2 >/dev/null && make install >/dev/null
fi
mkpc zlib 1.3.2 "-I${LP}/include" "-L${LP}/lib -lz" "zlib"
echo ok

echo "[STAGE 4/7] sqlite3"
if [ ! -f "$LP/lib/libsqlite3.a" ]; then
  cd "$SRC"
  C=$(find sqlite -name sqlite3.c | head -1); H=$(find sqlite -name sqlite3.h | head -1)
  gcc -O2 -fPIC -DSQLITE_THREADSAFE=1 -DSQLITE_ENABLE_FTS5 -DSQLITE_ENABLE_RTREE -DSQLITE_OMIT_LOAD_EXTENSION -c "$C" -o sqlite3.o
  ar rcs "$LP/lib/libsqlite3.a" sqlite3.o
  cp "$H" "$LP/include/sqlite3.h"
fi
mkpc sqlite3 3.53.0 "-I${LP}/include" "-L${LP}/lib -lsqlite3" "SQLite"
echo ok

echo "[STAGE 5/7] openssl"
if [ ! -f "$LP/lib/libssl.a" ]; then
  cd "$SRC/openssl-src"
  if [ ! -f libcrypto.a ]; then
    perl ./Configure linux-x86_64 no-shared no-tests no-docs --prefix="$LP" --openssldir="$LP/ssl" -fPIC
    make -j2 build_libs
  fi
  cp libssl.a libcrypto.a "$LP/lib/"
  mkdir -p "$LP/include/openssl" && cp include/openssl/*.h "$LP/include/openssl/"
fi
mkpc openssl 3.6.0 "-I${LP}/include" "-L${LP}/lib -lssl -lcrypto" "OpenSSL"
mkpc libssl 3.6.0 "-I${LP}/include" "-L${LP}/lib -lssl" "OpenSSL ssl"
mkpc libcrypto 3.6.0 "-I${LP}/include" "-L${LP}/lib -lcrypto" "OpenSSL crypto"
echo ok

echo "[STAGE 6/7] oniguruma"
if [ ! -f "$LP/lib/libonig.a" ]; then
  cd "$SRC/onig-src"
  cat > config.h <<'EOF'
#define HAVE_ALLOCA_H 1
#define HAVE_STDINT_H 1
#define HAVE_STDLIB_H 1
#define HAVE_STRING_H 1
#define HAVE_STRINGS_H 1
#define HAVE_INTTYPES_H 1
#define HAVE_SYS_TYPES_H 1
#define HAVE_SYS_STAT_H 1
#define HAVE_UNISTD_H 1
#define HAVE_LIMITS_H 1
#define HAVE_MEMORY_H 1
#define STDC_HEADERS 1
#define SIZEOF_INT 4
#define SIZEOF_SHORT 2
#define SIZEOF_LONG 8
#define SIZEOF_VOIDP 8
#define HAVE_PROTOTYPES 1
#define HAVE_MEMMOVE 1
#define HAVE_STRTOUL 1
EOF
  mkdir -p obj
  for c in $(find src -maxdepth 1 -name '*.c' ! -name '*_data*' ! -name 'mktable.c' | sort); do
    o="obj/$(basename "$c" .c).o"
    gcc -O2 -fPIC -DHAVE_CONFIG_H -I. -Isrc -c "$c" -o "$o" 2>err.log || { echo "FAIL $c"; tail -15 err.log; exit 1; }
  done
  ar rcs "$LP/lib/libonig.a" obj/*.o
  cp src/oniguruma.h src/oniggnu.h src/onigposix.h "$LP/include/"
fi
mkpc oniguruma 6.9.10 "-I${LP}/include" "-L${LP}/lib -lonig" "Oniguruma"
echo ok

echo "[STAGE 7/7] PHP $PHPV"
if [ ! -x /home/user/php/bin/php ]; then
  cd "$SRC/php-$PHPV"
  export LDFLAGS="-L$LP/lib"
  export CPPFLAGS="-I$LP/include"
  export OPENSSL_CFLAGS="-I$LP/include"
  export OPENSSL_LIBS="-L$LP/lib -lssl -lcrypto"
  ./configure \
    --prefix=/home/user/php \
    --disable-all \
    --enable-cli \
    --disable-cgi \
    --disable-phpdbg \
    --enable-mbstring \
    --enable-pdo --with-pdo-sqlite="$LP" --with-sqlite3="$LP" \
    --with-openssl \
    --with-zlib \
    --enable-bcmath \
    --enable-ctype \
    --enable-tokenizer \
    --enable-session \
    --enable-sockets \
    --enable-pcntl \
    --enable-posix \
    --enable-shmop \
    --enable-sysvmsg --enable-sysvsem --enable-sysvshm \
    --enable-fileinfo \
    --enable-phar \
    --enable-filter \
    --without-libxml \
    --without-pear \
    > configure.log 2>&1 || { echo "CONFIGURE FAILED"; tail -30 configure.log; exit 1; }
  # ensure all needed libs are in the final link
  sed -i 's/^EXTRA_LIBS = \(.*\)$/EXTRA_LIBS = \1 -lonig -lsqlite3 -lz/' Makefile
  awk '!/^EXTRA_LIBS/ || !seen {print} /^EXTRA_LIBS/ {seen=1}' Makefile > Makefile.tmp && mv Makefile.tmp Makefile
  make -j2 > make.log 2>&1 || { echo "MAKE FAILED"; tail -30 make.log; exit 1; }
  make install > install.log 2>&1
fi
cp "$SRC/php-$PHPV/php.ini-development" /home/user/php/lib/php.ini
sed -i 's/;date.timezone =/date.timezone = "Asia\/Dhaka"/; s/memory_limit = 128M/memory_limit = 512M/' /home/user/php/lib/php.ini

/home/user/php/bin/php -v
/home/user/php/bin/php -m | tr '\n' ' '; echo

echo "packing php-dist.tar.gz..."
tar czf "$REPO/tools_runtime/php-dist.tar.gz" -C /home/user php
ls -la "$REPO/tools_runtime/php-dist.tar.gz"
echo "[PHP BUILD COMPLETE]"
