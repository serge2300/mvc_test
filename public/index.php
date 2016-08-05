<?php

require_once __DIR__ . "/../app/config/config.php";
require_once __DIR__ . "/../vendor/autoload.php";

session_start();

try {
    $dispatcher = new \serge2300\MVCTest\Core\Dispatcher();
    $dispatcher->handle();
} catch (PDOException $e) {
    echo $e->getMessage();
} catch (Exception $e) {
    echo $e->getMessage();
}