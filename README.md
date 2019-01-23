# MailServer

This project is meant to setup a secure and production ready mail server
using Postfix, Dovecot and the related utilities.

## Important

**_THIS PROJECT IS IN DEVELOPMENT AND NOT READY!_**

## What is being installed

- Postfix (Mail Transport Agent)
- Dovecot (IMAP provider)
- Spamassassin (SPAM filtering)
- ClamAV (Virus filtering)
- Roundcube webmail (email web client app)
- PostfixAdmin (domains and mailbox configuration app)
- Automx (Automatic configuration provider for desktop mail clients)
- PostgreSQL (Database for virtual domains and accounts)

## Server Requirements

- **DO NOT RUN THE INSTALLER ON AN EXISTING SYSTEM!**
- A clean Ubuntu 18.04 server with at least 4GB of memory and the minimum of 20GB storage space.
- `php-cli` installed and ready on the system.

## DNS Requirements

- TODO

## Getting started

First we need to install the PHP interpreter.

```sh
sudo apt-get update -y
sudo apt-get upgrade -y
sudo apt-get install php-cli wget -y
```

Then we run the `installer` script and wait. Depending on the configuration of your
server, the installer process could take some time to finish.

_Substitute `mail.example.com` with the FDQN of your own mail server_

```sh
wget -qO- https://raw.githubusercontent.com/blendsdk/mailserver/master/install.php | sudo php mail.example.com
```

## After installation

TODO

---

## Change Log

### Version 0.9

Jan 2019, start of the development.
