#!/usr/bin/env php
<?php

class MailServerInstaller {

    /**
     * The version number
     * @var string
     */
    protected $VERSION = "0.9";
    protected $MAIL_USER = "mail";
    protected $MAIL_USER_GROUP = "";
    protected $MAIL_USER_UID = 5000;
    protected $MAIL_USER_PASSWORD = "";
    protected $SERVER_FDQN;
    protected $SPAM_ASSASSIN_CONFIG = "/etc/default/spamassassin";
    protected $last_error;

    /**
     * Creates an instance of MailServerInstaller
     */
    public function __construct() {
        global $argv;
        global $argc;

        $this->MAIL_USER_PASSWORD = base_convert(uniqid('pass', true), 10, 36);

        if (!empty(getenv("DEBUG"))) {
            $this->MAIL_USER = "mail_" . date('U');
            $this->MAIL_USER_UID = date('U');
        }

        $this->prompt_banner();
        if (posix_getuid() !== 0) {
            $this->prompt_error("You must run this script under root (sudo php install.php  my.domain.ltd)");
        }
        if ($argc !== 2) {
            $this->prompt_error("Missing the domain parameter! (sudo php install.php  my.domain.ltd)");
        }

        $this->SERVER_FDQN = $argv[1];

        $this->prompt_info("Installing " . $this->SERVER_FDQN);
        $this->update_system();
        if ($this->install_mail_user()) {
            if ($this->install_postgresql()) {
                if ($this->install_spamassassin()) {
                    if ($this->install_postfix()) {
                        $this->prompt_all_done();
                    }
                }
            }
        }
    }

    /**
     * Set postfix install defaults
     * @return type
     */
    protected function set_postfix_install_defaults() {
        $script = [
            "debconf-set-selections <<< \"postfix postfix/mailname string {$this->SERVER_FDQN}\"",
            "debconf-set-selections <<< \"postfix postfix/main_mailer_type string 'Internet Site'\"",
            "# end"
        ];
        $filename = tempnam("/tmp", "postfix-cfg-");
        file_put_contents($filename, implode("\n", $script));
        return $this->execute_command("bash {$filename}");
    }

    protected function configure_postfix() {
        $postconf = [
            "myhostname = ${$this->SERVER_FDQN}",
            "broken_sasl_auth_clients = no",
            "disable_vrfy_command = yes",
            "smtpd_banner = \$myhostname ESMTP"
        ];
        foreach ($postconf as $key => $value) {
            if (!$this->execute_command("postconf -e \"{$value}\"")) {
                $this->prompt_last_error();
            }
        }
        return true;
    }

    /**
     * Install and configure postfix
     * @return boolean
     */
    protected function install_postfix() {
        $this->prompt_info("Installing Postfix", false);
        if ($this->set_postfix_install_defaults()) {
            $this->install_system_package(["postfix"]);
            $this->install_system_package(["postfix-pgsql"]);
            if ($this->configure_postfix()) {
                $this->prompt_done();
                return true;
            }
        }
        $this->prompt_last_error();
    }

    /**
     * Install Spamassassin
     * @return boolean
     */
    protected function install_spamassassin() {
        $config = <<<STR
ENABLED=1
OPTIONS="--create-prefs --max-children 5 --helper-home-dir --username {$this->MAIL_USER} --syslog /var/log/spamd.log"
PIDFILE="/var/run/spamd.pid"
CRON=1
STR;
        $this->prompt_info("Installing Spamassassin", false);
        $this->install_system_package(['spamassassin']);
        file_put_contents($this->SPAM_ASSASSIN_CONFIG, $config);
        $this->prompt_done();
        return true;
    }

    /**
     * Install and configure the system user for handling virtual mails
     * @return boolean
     */
    protected function install_mail_user() {
        $this->prompt_info("Creating user accounts", false);
        if ($this->create_system_user($this->MAIL_USER, $this->MAIL_USER_GROUP, null, $this->MAIL_USER_UID)) {
            $this->prompt_done();
            return true;
        } else {
            $this->prompt_last_error();
        }
    }

    /**
     * Creates a system user account
     * @param type $username
     * @param type $group
     * @param type $home
     * @param type $uid
     * @return boolean
     */
    protected function create_system_user($username, $group = null, $home = null, $uid = null) {
        if (empty($group)) {
            $group = $username;
        }
        if (!empty($uid)) {
            $uid = "-u {$uid}";
        }
        if (empty($home)) {
            $home = "/home/{$username}";
        }
        if ($this->execute_command("groupadd " . $group)) {
            if ($this->execute_command("useradd -r ${uid} -g {$group} -d {$home} -s /usr/sbin/nologin {$username}")) {
                if ($this->execute_command("mkdir -p {$home}")) {
                    if ($this->execute_command("chmod -R 770 {$home}")) {
                        if ($this->execute_command("chown -R {$username}:{$group} {$home}")) {
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }

    /**
     * Install PostgreSQL
     */
    protected function install_postgresql() {
        $sql = <<<SQL
                create table domains (
                    id serial not null primary key,
                    name varchar not null unique,
                    is_active boolean not null default true
                );

                create table users (
                    id serial not null primary key,
                    username varchar not null unique,
                    password varchar not null
                );

                create table forwards (
                    id serial not null primary key,
                    from_email varchar not null,
                    to_email varchar not null,
                    unique(from_email, to_email)
                );

                create table domain_forwards (
                    id serial not null primary key,
                    from_domain varchar not null,
                    to_domain varchar not null,
                    unique(from_domain, to_domain)
                );
--
SQL;
        $this->prompt_info("Installing PostgreSQL", false);
        $this->install_system_package(['postgresql']);
        if ($this->execute_command("sudo -u postgres psql -c \"create role " . $this->MAIL_USER . " with login password '" . $this->MAIL_USER_PASSWORD . "';\"")) {
            if ($this->execute_command("sudo -u postgres psql -c \"create database " . $this->MAIL_USER . " owner " . $this->MAIL_USER . ";\"")) {

                $filename = tempnam("/tmp", "_script");
                file_put_contents($filename, $sql);
                $this->execute_command("chmod oug+rwx ${filename}");
                if ($this->execute_command("sudo -u {$this->MAIL_USER} psql -f {$filename}")) {
                    unlink(($filename));
                    $this->prompt_done();
                    return true;
                }
            }
        }
        $this->prompt_last_error();
    }

    /**
     * Updating the current system.
     */
    protected function update_system() {
        $this->prompt_info("Updating the system", false);
        shell_exec("apt-get update -y >> /dev/null 2>&1");
        shell_exec("apt-get upgrade -y >> /dev/null 2>&1");
        $this->prompt_done();
    }

    /**
     * Installing a one or more system packages
     * @param array $packages
     */
    protected function install_system_package(array $packages) {
        shell_exec("apt-get install -y " . implode(" ", $packages) . " >> /dev/null 2>&1");
    }

    /**
     * Executes a shell command suppressing the output.
     * If there is an error then the $this->last_error is set
     *
     * @param type $command
     * @return boolean
     */
    protected function execute_command($command, $prompt = "") {
        $last_err_file = tempnam("/tmp", ".mail_server_");

        if (!empty($prompt)) {
            $this->prompt_info($prompt);
        }

        if (file_exists($last_err_file)) {
            unlink($last_err_file);
        }

        shell_exec($command . " >> /dev/null 2>" . $last_err_file);

        if (file_exists($last_err_file)) {
            $this->last_error = trim(file_get_contents($last_err_file));
            unlink($last_err_file);
            return empty($this->last_error);
        } else {
            return true;
        }
    }

    /**
     * Prompt information when everything is done.
     */
    protected function prompt_all_done() {
        $this->prompt_info($this->MAIL_USER);
        $this->prompt_info("All done.");
    }

    /**
     * Prompt a ",done." message
     */
    protected function prompt_done() {
        $this->prompt_info(", done.");
    }

    /**
     * Prompts the last error and exists.
     */
    protected function prompt_last_error() {
        $this->prompt_error($this->last_error);
    }

    /**
     * Prompts a message
     * @param type $message
     * @param type $eol
     */
    protected function prompt_info($message, $eol = true) {
        echo "\033[93m" . $message . "\033[0m" . ($eol === true ? PHP_EOL : '');
    }

    /**
     * Prompts the banner
     */
    protected function prompt_banner() {
        echo PHP_EOL . "\033[96mMailServer Installer v" . $this->VERSION . "\033[0m" . PHP_EOL;
    }

    /**
     * Prompts an error and exists.
     * @param type $message
     */
    protected function prompt_error($message) {
        echo PHP_EOL . "\033[41mERROR: " . $message . "\033[0m" . PHP_EOL;
        exit;
    }

}

new MailServerInstaller();
