#!/usr/bin/env bash

if [[ $EUID -ne 0 ]]; then
   echo "This script must be run as root"
   exit 1
fi

echo -ne "\e[93mConfiguring firewall rules:..."
ufw allow ssh >> /dev/null 2>&1
ufw allow http >> /dev/null 2>&1
ufw allow https >> /dev/null 2>&1
ufw allow 587 >> /dev/null 2>&1 # smtp
ufw allow 456 >> /dev/null 2>&1 # secure smtp
ufw allow 143 >> /dev/null 2>&1 # imap
ufw allow 998 >> /dev/null 2>&1 # imap tls
ufw --force enable >> /dev/null 2>&1
echo -e "\e[93mdone."
exit $?
