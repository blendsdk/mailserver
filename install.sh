#!/usr/bin/env bash

{ # this ensures the entire script is downloaded #

MYSQL_PASSWORD=`openssl rand -base64 10`

if [[ $EUID -ne 0 ]]; then
   echo "This script must be run as root"
   exit 1
fi

usage()
{
    echo "usage: install -d mail.mydomain.ltd"
}


domain_name=

while [ "$1" != "" ]; do
    case $1 in
        -d | --domain ) shift
                        domain_name=$1
                        ;;
        -h | --help )           usage
                                exit
                                ;;
        * )                     usage
                                exit 1
    esac
    shift
done

if [ -z ${domain_name} ]; then
    echo "No domain name provided!"
    usage
    exit 2;
fi

# force set home to root
export HOME=/root

# update and upgrade this system
apt-get update -y
apt-get upgrade -y

# install spamassassin
apt-get install -qq -y spamassassin
cp ./spamassassin /etc/default/spamassassin

# install postfix
debconf-set-selections <<< "postfix postfix/mailname string ${domain_name}"
debconf-set-selections <<< "postfix postfix/main_mailer_type string 'Internet Site'"
apt-get install -y postfix
apt-get install -y postfix-pgsql

# lets encrypt
apt-get install software-properties-common -y
add-apt-repository universe -y
add-apt-repository ppa:certbot/certbot -y
apt-get update -y
apt-get install certbot -y

} # this ensures the entire script is downloaded #