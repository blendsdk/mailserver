#!/usr/bin/env bash

{ # this ensures the entire script is downloaded #

MYSQL_PASSWORD=`openssl rand -base64 10`

if [[ $EUID -ne 0 ]]; then
   echo "This script must be run as root"
   exit 1
fi

# force set home to root
export HOME=/root

echo "Installing Expect"
command apt-get -qq install expect > /dev/null || {
   echo >&2 'Failed to install Expect!'
  exit 2
}

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

tee ~/secure_our_mysql.sh > /dev/null << EOF
spawn $(which mysql_secure_installation)

expect "Enter password for user root:"
send "$MYSQL_ROOT_PASSWORD\r"

expect "Press y|Y for Yes, any other key for No:"
send "y\r"

expect "Please enter 0 = LOW, 1 = MEDIUM and 2 = STRONG:"
send "2\r"

expect "Change the password for root ? ((Press y|Y for Yes, any other key for No) :"
send "n\r"

expect "Remove anonymous users? (Press y|Y for Yes, any other key for No) :"
send "y\r"

expect "Disallow root login remotely? (Press y|Y for Yes, any other key for No) :"
send "y\r"

expect "Remove test database and access to it? (Press y|Y for Yes, any other key for No) :"
send "y\r"

expect "Reload privilege tables now? (Press y|Y for Yes, any other key for No) :"
send "y\r"

EOF

echo "Configuring MySQL"
command expect ~/secure_our_mysql.sh || {
  echo >&2 'Failed to configure MySQL!'
  exit 2
}

# cleanup
# rm -v ~/secure_our_mysql.sh

echo "==> MySQL Password is: ${MYSQL_PASSWORD}"

} # this ensures the entire script is downloaded #