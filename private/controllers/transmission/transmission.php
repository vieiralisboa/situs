<?php

/**
 * Transmission controller
 */
class Transmission_Controller
{
    public function get($request) {
        #return "offline";
        $config = Router::$controller_config;
        $dir = "/shares";
        if(isset($config->dir)) $dir = $config->dir;
        return response($request, $dir);
    }
}

require dirname(__FILE__)."/navigator.php";
