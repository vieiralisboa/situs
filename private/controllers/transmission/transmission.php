<?php

/**
 * Transmission class
 */
class Transmission_Controller
{
    public static function get($request) {
        $config = Router::$controller_config;
        foreach (array("dir","ignore") as $name)
    		if(isset($config->$name)) $request->$name = $config->$name;

        $request->url = $_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST'].$request->pre."/";
        
        echo response($request);
    }
}

require dirname(__FILE__)."/navigator.php";
