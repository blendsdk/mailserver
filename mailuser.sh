#!/usr/bin/env bash

if [[ $EUID -ne 0 ]]; then
   echo "This script must be run as root"
   exit 1
fi

echo -e "\e[93mCreating mailserver user and group."
groupadd ${MAIL_GROUP}
useradd -r -u ${MAIL_UID} -g ${MAIL_USER} -d ${MAIL_HOME} -s /usr/sbin/nologin -c "Virtual Mail User" ${MAIL_USER}
mkdir -p ${MAIL_HOME}
chmod -R 770 ${MAIL_HOME}
chown -R ${MAIL_USER}:${MAIL_GROUP} ${MAIL_HOME}
