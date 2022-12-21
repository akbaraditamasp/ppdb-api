<?php

use Bramus\Router\Router;
use Siluet\App;
use Siluet\BodyParser;
use Siluet\Cors;
use Siluet\Eloquent;
use Siluet\Notif;

require __DIR__ . "/../vendor/autoload.php";

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->load();

App::boot();
BodyParser::boot();
Eloquent::boot();
Notif::boot();

$router = new Router();
Cors::boot($router);
$router->setNamespace('\Controller');

require __DIR__ . "/../routes/routes.php";

$router->run();
