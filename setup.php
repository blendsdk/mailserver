<?php

require_once dirname(__FILE__) . '/vendor/autoload.php';

use Colors\Color;

class MailSetup {

    protected $last_error;
    protected $mail_user;
    protected $password;
    protected $server_fdqn;

    function __construct($server_fdqn) {
        $this->mail_user = "mailserver";
        $this->password = base_convert(uniqid('pass', true), 10, 36);
        $this->server_fdqn = $server_fdqn;
    }

    protected function prompt_info($message) {
        $c = new Color();
        echo $c($message)->yellow;
    }

    protected function prompt_done() {
        $c = new Color();
        echo $c('done.')->cyan . PHP_EOL;
    }

    protected function prompt_last_error() {
        $c = new Color();
        echo PHP_EOL . $c($this->last_error)->red . PHP_EOL;
    }

    protected function install_system_package(array $packages) {
        shell_exec("apt-get install -y " . implode(" ", $packages) . " >> /dev/null 2>&1");
    }

    protected function execute_command($command) {
        $last_err_file = "/tmp/.last_error.txt";

        if(file_exists($last_err_file)) {
            unlink($last_err_file);
        }

        shell_exec($command . " >> /dev/null 2>" . $last_err_file);

        if(file_exists($last_err_file)) {
            $this->last_error = file_get_contents($last_err_file);
            // unlink($last_err_file);
            return false;
        } else {
            return true;
        }

    }

    protected function install_postgresql() {
        $this->prompt_info("Installing PostgreSQL...");
        $this->install_system_package(["postgresql"]);
        if($this->execute_command("sudo -u postgres psql -c \"create role " . $this->mail_user . " with login password '" . $this->password .  "';\"")) {
            if($this->execute_command("sudo -u postgres psql -c \"create database " . $this->server_fdqn . " owner " . $this->mail_user . "\";")) {
                $this->prompt_done();
                return true;
            }
        }
        $this->prompt_last_error();
        return false;
    }

    public function run() {
        $c = new Color();
        echo $c("Installing " . $this->server_fdqn)->white()->bold()->highlight('green') . PHP_EOL;
        $this->install_postgresql();
    }
}

$setup = new MailSetup(getenv('MAILSERVER_FDQN'));
$setup->run();

