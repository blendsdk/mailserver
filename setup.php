<?php

require_once dirname(__FILE__) . '/vendor/autoload.php';

class MailSetup {

    protected $color;

    function __construct() {
        $this->color = new Colors\Color();
    }

    protected function prompt_info($message) {
        echo $this->color($message)->yellow;
    }

    protected function prompt_done() {
        echo $this->color('done.')->cyan . PHP_EOL;
    }

    protected function install_system_package(array $packages) {
        shell_exec("apt-get install -y " . implode(" ", $packages) . " /dev/null 2>&1");
    }

    protected function install_postgresql() {
        $this->prompt_info("Installing PostgreSQL...");
        $this->install_system_package(["postgresql"]);
        $this->prompt_done();
    }

    public function run() {
        $this->install_postgresql();
    }
}

$setup = new MailSetup();
$setup->run();

