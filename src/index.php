<?php
// Point d'entrée simple: délègue au Router
require_once __DIR__ . '/Router.php';

$router = new Router();
$router->dispatch();
