<?php

/**
 * Download
 * PHP 5.2.6-1+lenny9
 */
class PHP52_Controller {
    
    // GET /download/file
    public function get($request) {
        return array(
        	'version' => phpversion(),
        	//'credits' => phpcredits()
        );
    }
}