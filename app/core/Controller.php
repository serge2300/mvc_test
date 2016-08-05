<?php

namespace serge2300\MVCTest\Core;

use serge2300\MVCTest\Core\Router;

class Controller
{

    /**
     * List of variables that are passed to the view
     * 
     * @var array
     */
    public $view_vars = [];

    /**
     * List of templates to load
     * 
     * @var array
     */
    public $templates = [];

    public function __construct() {
        // Add current controller/action to the templates list
        $this->templates[] = Router::getController() . '/' . Router::getAction();
        // Show alerts
        if (isset($_SESSION['alert'])) {
            echo $_SESSION['alert'];
            unset($_SESSION['alert']);
        }
    }

    /**
     * Set view variables
     * 
     * @param array $vars
     */
    protected function view($vars)
    {
        $this->view_vars += $vars;
    }

    /**
     * Add a template to the beginning of the list
     * 
     * @param $template
     */
    protected function insertAfter($template)
    {
        array_unshift($this->templates, $template);
    }

    /**
     * Add a template to the end of the list
     *
     * @param $template
     */
    protected function insertBefore($template)
    {
        array_push($this->templates, $template);
    }

    /**
     * Set page title
     *
     * @param $title
     */
    protected function setTitle($title)
    {
        $this->view_vars['title'] = $title;
    }
}