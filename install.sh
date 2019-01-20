#!/usr/bin/env bash

{ # this ensures the entire script is downloaded #

MYSQL_PASSWORD=`openssl rand -base64 10`

if [[ $EUID -ne 0 ]]; then
   echo "This script must be run as root"
   exit 1
fi

# force set home to root
export HOME=/root

echo "Installing MySQL"
command apt-get -qq install mysql-server -y 2>/dev/null || {
  echo >&2 'Failed to install MySQL!'
  exit 2
}

echo "Starting MySQL"
command service mysql restart 2> /dev/null || {
  echo >&2 'Failed to start MySQL!'
  exit 2
}

echo "Configuring MySQL"
command echo -e "y\n" | mysql_secure_installation || {
  echo >&2 'Failed to configure MySQL!'
  exit 2
}

# cleanup
# rm -v ~/secure_our_mysql.sh

echo "==> MySQL Password is: ${MYSQL_PASSWORD}"

} # this ensures the entire script is downloaded #