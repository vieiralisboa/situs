<?php

/**
 * PHP
 */
class PHP_Controller {
    
    // GET 
    public function get($request) {
        return array(
        	'version' => phpversion(),
        	//'credits' => phpcredits()
        );
    }
}