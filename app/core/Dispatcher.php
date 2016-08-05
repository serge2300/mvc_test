<?php

namespace serge2300\MVCTest\Core;

use serge2300\MVCTest\Core\Router;

class Dispatcher
{

    /**
     * Handle a request to respective controller
     * 
     * @throws \Exception
     */
    public function handle()
    {
        $controller = Router::getController();
        $controller_name = ucfirst($controller) . "Controller";
        $action = Router::getAction();
        $action_name = "{$action}Action";
        $param = Router::getParam();
        $class = "\\serge2300\\MVCTest\\Controllers\\$controller_name";

        // Controller/class checks
        if (!file_exists(__DIR__ . "/../controllers/" . $controller_name . ".php"))
            throw new \Exception("File $controller_name.php doesn't exist");
        if (!class_exists($class))
            throw new \Exception("Class $controller_name doesn't exist");
        if (!is_subclass_of($class, "\\serge2300\\MVCTest\\Core\\Controller"))
            throw new \Exception("Class $controller_name must extend Controller class");

        // View/action check
//        if (!file_exists(__DIR__ . "/../views/$controller"))
//            throw new \Exception("Folder $controller doesn't exist in views folder");
//        if (!file_exists(__DIR__ . "/../views/$controller/$action.twig"))
//            throw new \Exception("View file $action doesn't exist in views folder $controller");
        if (!method_exists($class, $action_name))
            throw new \Exception("Action $action_name doesn't exist in class $controller_name");

        // Instantiate a class and execute a method
        $load = new $class;
        $load->$action_name($param);

        $output = '';
        // Load templates
        foreach ($load->templates as $template) {
            $template = explode('/', trim($template, '/'));
            $twig = new \Twig_Environment(new \Twig_Loader_Filesystem(__DIR__ . "/../views/{$template[0]}/"));
            $output .= $twig->render("{$template[1]}.twig", $load->view_vars);
        }
        
        // Echo the output
        echo $output;
    }
}