#!/usr/bin/env bash

{ # this ensures the entire script is downloaded #

MYSQL_PASSWORD=`openssl rand -base64 10`

if [[ $EUID -ne 0 ]]; then
   echo "This script must be run as root"
   exit 1
fi

# force set home to root
export HOME=/root

command apt-get install mysql-server -y || {
  echo >&2 'Failed to install MySQL!'
  exit 2
}

command service mysqld restart || {
  echo >&2 'Failed to start MySQL!'
  exit 2

}

# securing mysql
command mysql -e "UPDATE mysql.user SET Password=PASSWORD('${MYSQL_PASSWORD}') WHERE User='root';"
mysql -e "DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');"
mysql -e "DELETE FROM mysql.user WHERE User='';"
mysql -e "DROP DATABASE test;"
mysql -e "FLUSH PRIVILEGES;"

echo "==> MySQL Password is: ${MYSQL_PASSWORD}"

} # this ensures the entire script is downloaded #