<?php

namespace serge2300\MVCTest\Core;

class Router
{

    /**
     * Get controller/action/param out of GET request
     * 
     * @return array
     */
    public static function getRoute()
    {
        if (isset($_GET['url']) and !empty($_GET['url'])) {
            $route = explode('/', trim($_GET['url'], '/'));
            return [
                'controller' => strtolower($route[0]),
                'action'     => isset($route[1]) ? strtolower($route[1]) : 'index',
                'param'      => isset($route[2]) ? strtolower($route[2]) : null
            ];
        }
        return [
            'controller' => 'index',
            'action'     => 'index',
            'param'      => null
        ];
    }

    /**
     * Get current controller name
     * 
     * @return mixed
     */
    public static function getController()
    {
        return self::getRoute()['controller'];
    }

    /**
     * Get current action name
     *
     * @return mixed
     */
    public static function getAction()
    {
        return self::getRoute()['action'];
    }

    /**
     * Get current param name
     *
     * @return mixed
     */
    public static function getParam()
    {
        return self::getRoute()['param'];
    }

    /**
     * Redirect to specified location
     * 
     * @param $location
     */
    public static function redirectTo($location) {
        header("Location: /$location");
        exit;
    }
}