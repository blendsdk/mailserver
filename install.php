#!/usr/bin/env php
<?php

class MailServerInstaller {

    /**
     * The version number
     * @var string
     */
    protected $VERSION = "0.9";
    protected $USERNAME = "mail";
    protected $PASSWORD;
    protected $SERVER_FDQN;
    protected $last_error;

    /**
     * Creates an instance of MailServerInstaller
     */
    public function __construct() {
        global $argv;
        global $argc;

        $this->PASSWORD = base_convert(uniqid('pass', true), 10, 36);

        if(getenv("DEBUG")) {
            $this->USERNAME = "mail_" . date('U');
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
        if ($this->install_postgresql()) {
            $this->prompt_all_done();
        }
    }

    /**
     * Install PostgreSQL
     */
    protected function install_postgresql() {
        $this->prompt_info("Installing PostgreSQL", false);
        $this->install_system_package(['postgresql']);
        if ($this->execute_command("sudo -u postgres psql -c \"create role " . $this->USERNAME . " with login password '" . $this->PASSWORD . "';\"")) {
            if ($this->execute_command("sudo -u postgres psql -c \"create database " . $this->USERNAME . " owner " . $this->USERNAME . ";\"")) {
                $this->prompt_done();
                return true;
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
        echo "\033[41mERROR: " . $message . "\033[0m" . PHP_EOL;
        exit;
    }

}

new MailServerInstaller();
