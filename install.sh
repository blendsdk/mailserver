#!/usr/bin/env bash

{ # this ensures the entire script is downloaded #

if [[ $EUID -ne 0 ]]; then
   echo -e >&2 "\e[91mThis script must be run as root!"
   exit 1
fi

usage()
{
    echo "usage: install -d mail.mydomain.ltd"
}

# mailserver
MAILSERVER_FDQN=

while [ "$1" != "" ]; do
    case $1 in
        -d | --domain ) shift
                        MAILSERVER_FDQN=$1
                        ;;
        -h | --help )           usage
                                exit
                                ;;
        * )                     usage
                                exit 1
    esac
    shift
done

if [ -z ${MAILSERVER_FDQN} ]; then
    echo -e >&2 "\e[91mNo domain name provided!"
    usage
    exit 2;
fi

export MAILSERVER_FDQN

echo -ne "\e[93mPreparing..."
apt-get update -y >> /dev/null 2>&1
apt-get upgrade -y >> /dev/null 2>&1
echo -e ",\e[96mdone."

if ! [ -x "$(which php)" ]; then
    echo -ne "\e[93mInstalling PHP:..."
    apt-get install php-cli -y >> /dev/null 2>&1
    echo -e ",\e[96mdone."
fi

if ! [ -x "$(which composer)" ]; then
    echo -ne "\e[93mInstalling Composer:..." && \
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" >> /dev/null && \
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer >> /dev/null && \
    php -r "unlink('composer-setup.php');" && \
    echo -e ",\e[96mdone."
fi

if ! [ -x "$(which git)" ]; then
    echo -ne "\e[93mInstalling GIT:..." && \
    apt-get install git -y >> /dev/null 2>&1
    echo -e ",\e[96mdone."
fi

if [ -d "./mailserver" ]; then
    rm -fR ./mailserver
fi

echo -ne "\e[93mGetting setup files..."
git clone --depth=1 https://github.com/blendsdk/mailserver.git >> /dev/null 2>&1
cd mailserver
composer update >> /dev/null 2>&1
echo -e ",\e[96mdone."

php setup.php ${MAILSERVER_FDQN}

cd ..

} # this ensures the entire script is downloaded #