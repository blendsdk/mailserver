#!/usr/bin/env bash

{ # this ensures the entire script is downloaded #

if [[ $EUID -ne 0 ]]; then
   echo -e >&2 "\e[91mThis script must be run as root!"
   exit 1
fi

echo -ne "\e[93mPreparing..."
apt-get update -y >> /dev/null 2>&1
apt-get upgrade -y >> /dev/null 2>&1
echo -e ",\e[96mdone."

if ! [ -x "$(which php)" ]; then
    echo -ne "\e[93mInstalling PHP:..."
    apt-get install php-cli -y >> /dev/null 2>&1
    echo -e ",\e[96mdone."
fi

} # this ensures the entire script is downloaded #