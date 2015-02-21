<?php

/**
 * PHP
 */
class PHP_Controller
{

    // GET
    public function get($request) {

        Router::route('/PHP', function() {
            return array('version' => phpversion());
        });

        Router::route('/PHP/credits', function() {
            Router::$json = false;
            phpcredits();
        });

        Router::route('/PHP/info', function() {
        	Router::$json = false;
            phpinfo();
        });

        Router::route('/PHP/SERVER', function() {
            return $_SERVER;
        });

        Router::route('/PHP/GLOBALS', function() {
            return $GLOBALS;
        });

    }
}
