<?php

/**
 * PHP
 */
class PHP_Controller {

    // GET
    public function get($request) {

        Router::route('/PHP', function() {
            return array(
        	'version' => phpversion(),
        	//'credits' => phpcredits()
        	);
        });

        Router::route('/PHP/info', function() {
        	Router::$json = false;
            phpinfo();
        });
    }
}