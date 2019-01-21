#!/usr/bin/env bash

if [[ $EUID -ne 0 ]]; then
   echo "This script must be run as root"
   exit 1
fi

echo -ne "\e[93mInstalling PostgreSQL..."
apt-get install postgresql -y >> /dev/null 2>&1
sudo -u postgres psql -c "create role mail with login password '${DB_PASSWORD}';" >> /dev/null 2>&1
sudo -u postgres psql -c "create database mailserver owner mail;" >> /dev/null 2>&1
echo -ne ",\e[93mDone"
