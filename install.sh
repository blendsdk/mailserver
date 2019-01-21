#!/usr/bin/env bash

{ # this ensures the entire script is downloaded #

MYSQL_PASSWORD=`date +%s | sha256sum | base64 | head -c 10`

if [[ $EUID -ne 0 ]]; then
   echo "This script must be run as root"
   exit 1
fi

usage()
{
    echo "usage: install -d mail.mydomain.ltd"
}

pause() {
   read -rsp $'Press any key to continue...\n' -n1 key
}

# mailserver
mailserver=
vmailhome=/home/vmail

while [ "$1" != "" ]; do
    case $1 in
        -d | --domain ) shift
                        mailserver=$1
                        ;;
        -h | --help )           usage
                                exit
                                ;;
        * )                     usage
                                exit 1
    esac
    shift
done

if [ -z ${mailserver} ]; then
    echo "No domain name provided!"
    usage
    exit 2;
fi

# force set home to root
export HOME=/root

# updateing an upgrading the system
apt-get update -y
apt-get upgrade -y

# setting the firewall
command ./firewall.sh || {
  echo >&2 'Firewall configuration failed!'
  exit 2
}

# installing postgresql
command ./postgresql.sh || {
  echo >&2 'PostgreSQL configuration failed!'
  exit 2
}

exit;

# # update and upgrade this system and add needed repos
# # lets encrypt
# apt-get update -y
# apt-get install software-properties-common -y
# add-apt-repository universe -y
# add-apt-repository ppa:certbot/certbot -y
# apt-get update -y
# apt-get upgrade -y

# # install postgresql
# apt-get install postgresql -y

# # install spamassassin
# apt-get install -qq -y spamassassin
# cp ./spamassassin /etc/default/spamassassin

# # install postfix
# debconf-set-selections <<< "postfix postfix/mailname string ${mailserver}"
# debconf-set-selections <<< "postfix postfix/main_mailer_type string 'Internet Site'"
# apt-get install -y postfix
# apt-get install -y postfix-pgsql

# # lets encrypt
# apt-get install software-properties-common -y
# add-apt-repository universe -y
# add-apt-repository ppa:certbot/certbot -y
# apt-get update -y
# apt-get install certbot -y

# # configure postfix

# # create virtual mail user account and group
# groupadd vmail
# useradd -r -u 2000 -g vmail -d ${vmailhome} -s /usr/sbin/nologin -c "Virtual Mail User" vmail
# mkdir -p ${vmailhome}
# chmod -R 770 ${vmailhome}
# chown -R vmail:vmail ${vmailhome}

# #set the mailname, for postfix myorigin
# echo ${mailserver} > /etc/mailname
# # setting the hostname
# postconf -e "myhostname = ${mailserver}"
# # no more old clients
# postconf -e "broken_sasl_auth_clients = no"
# # do not allow VERIFY command on smtp
# postconf -e "disable_vrfy_command = yes"
# # change the default banner
# postconf -e "smtpd_banner = \$myhostname ESMTP"

# echo "Run: ufw enable"

} # this ensures the entire script is downloaded #