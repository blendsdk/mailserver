<?php

require_once dirname(__FILE__) . '/vendor/autoload.php';

use Colors\Color;
$c = new Color();

echo $c('Hello World!')->white()->bold()->highlight('green') . PHP_EOL;
