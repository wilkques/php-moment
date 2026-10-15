#!/bin/sh
# Runs the one PHPUnit suite on PHP 5.3 .. 8.4, each with the PHPUnit major that supports it (phpunit.xml for
# PHPUnit 4.8 - 9.6, phpunit.10.xml for PHPUnit 10.5+), in throwaway copies of the package (so nothing is
# installed into the working tree).
#   tools/run-matrix.sh            all versions
#   tools/run-matrix.sh 5.3 8.4    only these labels (5.3 5.6 5.6-32bit 7.0 ... 8.4)
# Run it from /var/www/packages (docker can only see that directory). Output: one summary per version.
#
# 5.6-32bit runs on i386/php, a 32-bit PHP where an int is only 31 bits wide.
#   5.3 needs Composer 2.2 LTS (its autoloader still loads on PHP 5.3) with the platform pinned to 5.3.29,
#   and the "php53-test" image because the official php:5.3.29-cli has neither zlib nor mbstring.
SPECS="5.3:5.3:^4.8:lts:php53-test 5.6:5.6:^5.7:lts:php:5.6-cli 5.6-32bit:5.6:^5.7:lts:i386/php:5.6-cli 7.0:7.0:^6.5:std:php:7.0-cli 7.1:7.1:^7.5:std:php:7.1-cli 7.2:7.2:^8.5:std:php:7.2-cli 7.4:7.4:^9.6:std:php:7.4-cli 8.0:8.0:^9.6:std:php:8.0-cli 8.1:8.1:^10.5:std:php:8.1-cli 8.2:8.2:^11.0:std:php:8.2-cli 8.3:8.3:^12.0:std:php:8.3-cli 8.4:8.4:*:std:php:8.4-cli"
# spec = <label>:<php version>:<phpunit constraint>:<composer mode>:<docker image>
# The config file follows the PHPUnit major: phpunit.10.xml for the 8.x labels from 8.1 on, phpunit.xml otherwise.

for spec in $SPECS; do
  label=$(echo $spec | cut -d: -f1); php=$(echo $spec | cut -d: -f2); pu=$(echo $spec | cut -d: -f3)
  mode=$(echo $spec | cut -d: -f4); img=$(echo $spec | cut -d: -f5-)
  if [ -n "$1" ]; then case " $* " in *" $label "*) ;; *) continue;; esac; fi

  case $label in 8.1|8.2|8.3|8.4) cfg=phpunit.10.xml;; *) cfg=phpunit.xml;; esac

  d=/var/www/packages/moment-run-$label
  rm -rf $d; mkdir -p $d
  (cd moment && tar cf - --exclude=vendor --exclude=node_modules --exclude=composer.lock .) | tar xf - -C $d
  setup="git config --global --add safe.directory '*'"
  [ "$mode" = lts ] && setup="$setup; composer self-update --2.2 -q"
  docker run --rm -v $d:/w -w /w -e COMPOSER_NO_SECURITY_BLOCKING=1 composer:2 sh -c "$setup; composer config platform.php $php.99; composer require --dev phpunit/phpunit:'$pu' -W --no-interaction --no-progress -q" >/dev/null 2>&1
  echo "=== PHP $label (phpunit $pu) on $img"
  docker run --rm -v $d:/w -w /w $img php vendor/bin/phpunit -c $cfg 2>&1 | sed 's/\x1b\[[0-9;]*m//g' | grep -E "^(OK|Tests:|FAILURES|ERRORS)|Fatal|^[0-9]+\) |Skipped|skipped|Deprecations|issues" | head -8
  rm -rf $d
done
