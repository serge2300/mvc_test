<?php

$config = [
    'database' => [
        'driver'   => 'mysql',
        'host'     => 'localhost',
        'port'     => '3306',
        'dbname'   => 'mvc',
        'username' => 'root',
        'password' => 'root'
    ],
    'captcha' => [
        'url'        => 'https://www.google.com/recaptcha/api/siteverify',
        'site_key'   => '6Len8B0TAAAAAC2fohUhUUvhjnNuqK4xuFyp_9cI',
        'secret_key' => '6Len8B0TAAAAALiO3hBTF4OTacwuq2c9F39BI4qX'
    ],
    'sms' => [
        'url'    => 'https://smsc.ru/sys/send.php',
        'login'  => 'testmvc',
        'psw'    => 'testmvc',
        'sender' => 'Test'
    ]
];