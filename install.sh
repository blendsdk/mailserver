#!/usr/bin/env bash

{ # this ensures the entire script is downloaded #

MYSQL_PASSWORD=`openssl rand -base64 10`

if [[ $EUID -ne 0 ]]; then
   echo "This script must be run as root"
   exit 1
fi

# force set home to root
export HOME=/root

echo "Installing PostgreSQL"
command apt-get -qq install postgresql -y 2>/dev/null || {
  echo >&2 'Failed to install PostgreSQL!'
  exit 2
}

} # this ensures the entire script is downloaded #