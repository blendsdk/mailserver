#!/usr/bin/env bash

echo -ne "\e[93mCreating mailserver user and group..."
groupadd ${MAIL_GROUP}
useradd -r -u ${MAIL_UID} -g ${MAIL_USER} -d ${MAIL_HOME} -s /usr/sbin/nologin -c "Virtual Mail User" ${MAIL_USER}
mkdir -p ${MAIL_HOME}
chmod -R 770 ${MAIL_HOME}
chown -R ${MAIL_USER}:${MAIL_GROUP} ${MAIL_HOME}
echo -e "\e[93mdone."
exit $?
