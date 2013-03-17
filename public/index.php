<?php

error_reporting(0);

Router::run();

/**
 * Auto loads classes
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
 * loads a required files or quits quietly
 */
function load($file){
    if(!file_exists($file)) return false;
    require $file;
    return true;
}