<?php


use RestClient\SayHello;

require_once __DIR__ . './vendor/autoload.php'; // Autoload files using Composer autoload

echo SayHello::world();