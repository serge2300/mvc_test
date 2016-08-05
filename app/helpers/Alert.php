<?php

namespace serge2300\MVCTest\Helpers;

class Alert
{
    public static function set($message, $type = 'info')
    {
        $_SESSION['alert'] = "<div class=\"alert alert-$type\">$message</div>";
    }
}