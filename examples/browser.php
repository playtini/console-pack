<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Playtini\ConsolePack\Browser;

$browser = new Browser(__DIR__ . '/../var/cache');
$html = $browser->get('https://api.ipify.org?format=json');

echo "\n";
var_dump($browser->getLastStatusCode());
echo "\n";
print_r($browser->getLastResponseHeaders());
echo "\n";
var_dump($html);
echo "\n";
