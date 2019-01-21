<?php

require_once dirname(__FILE__) . '/vendor/autoload.php';

use Colors\Color;
$color = new Color();

function prompt_info($message) {
    echo $color($message)->yellow;
}

function prompt_done() {
    echo $color('done.')->cyan . PHP_EOL;
}

function install_system_package(array $packages) {
    shell_exec("apt-get install -y " . implode(" ", $packages) . " /dev/null 2>&1");
}

function install_postgresql() {
    prompt_info("Installing PostgreSQL...");
    install_system_package(["postgresql"]);
    prompt_done();
}

install_postgresql();

