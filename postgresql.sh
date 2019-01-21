#!/usr/bin/env bash

if [[ $EUID -ne 0 ]]; then
   echo "This script must be run as root"
   exit 1
fi

apt-get install postgresql -y
psql -c "create role mail with login password '${DB_PASSWORD}';"
psql -c "create database mailserver owner mail;"