#!/usr/bin/env bash

if [[ $EUID -ne 0 ]]; then
   echo "This script must be run as root"
   exit 1
fi

ufw allow ssh
ufw allow http
ufw allow https
ufw allow 587 # smtp
ufw allow 456 # secure smtp
ufw allow 143 # imap
ufw allow 998 # imap tls
ufw force --enable