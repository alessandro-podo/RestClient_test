<?php


use RestClient\Attribute\Type;
use RestClient\RestClient;

require_once __DIR__ . '/vendor/autoload.php'; // Autoload files using Composer autoload
require_once __DIR__ . '/Implementierung/EntityOKBasicAuthenticator.php';

$entity =  (new EntityOKBasicAuthenticator())->setDisplay('asdsad')->setId(3);

/*$request = (new \RestClient\RequestBuilder())
    ->setEntity($entity)
    ->getRequest()
;*/


$att = (new \ReflectionClass(\RestClient\Attribute\HttpMethod::class))->getConstants();;
dump($att);