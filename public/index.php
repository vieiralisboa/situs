<?php

include "../activity.php";

/**
 * Situs - A PHP Framework
 *
 * @package  Situs
 * @version  0.0.0
 * @author   José Vieira Lisboa <jose.vieira.lisboa@gmail.com>
 * @link     http://situs.pt
 */

//--------------------------
// Uncomment for production
//--------------------------
#error_reporting(0);

Router::run();

/**
 * Auto Load Class
 * Auto loads the required class if the class exists
 * in the directory paths listed in the $paths array.
 * @param (string) $className the class name
 */
function __autoload($className) {
    $className = strtolower($className);
    $base = dirname(dirname(__FILE__));
    $paths = array("/app/", "/storage/");
    foreach($paths as $path){
    	if(load("$base$path$className.php")) break;
    }
}

/**
 * Load File
 * Requires a file or quits silently
 */
function load($file){
    if(!file_exists($file)) return false;
    require $file;
    return true;
}