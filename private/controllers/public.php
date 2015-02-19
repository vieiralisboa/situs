<?php

/**
 * Public controller
 */
class Public_Controller
{
    public function get($request) {
        $config = Router::$controller_config;
        $root = "/mnt/Public";
        if(isset($config->root)) $root = $config->root;
        return response($request, $root);
    }
}

include dirname(__FILE__)."/transmission/navigator.php"; 
