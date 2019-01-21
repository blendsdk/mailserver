#!/usr/bin/env bash

if [[ $EUID -ne 0 ]]; then
   echo "This script must be run as root"
   exit 1
fi

apt-get install postgresql -y
sudo -u postgres psql -c "create role mail with login password '${DB_PASSWORD}';"
sudo -u postgres psql -c "create database mailserver owner mail;"
sudo -u postgres psql -h 127.0.0.1
