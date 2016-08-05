<?php

namespace serge2300\MVCTest\Controllers;

use serge2300\MVCTest\Core\Controller;
use serge2300\MVCTest\Core\Router;

class IndexController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->insertAfter('layouts/header');
        $this->insertBefore('layouts/footer');
    }

    public function indexAction() 
    {
        $this->setTitle('Welcome');

        if (isset($_SESSION['user']['id']))
            Router::redirectTo("user/profile/{$_SESSION['user']['id']}");
    }
}