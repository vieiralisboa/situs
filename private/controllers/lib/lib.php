<?php

/*
 * Lib
 */
class Lib_Controller {
    public function get($request) {

        Router::route('/lib', function($request) {
            return Router::$controller_config->libs;
        });

        Router::route('/lib/config', function($request) {
            return Router::$controller_config;
        });

        Router::route('/lib/help', function($request) {
            return Router::$controller_config->help;
        });

        Router::route('/lib/:lib', function($request) {
            $lib = $request->data['lib'];
            $path = WWW.Router::$controller_config->path;
            $file = $path.Router::$controller_config->libs->$lib;

            if(!file_exists($file)) Util::quit(404);
            Util::serve($file);
        });

        Router::route('/lib/dl/:lib', function($request) {
            $lib = $request->data['lib'];
            $path = WWW.Router::$controller_config->path;
            $file = $path.Router::$controller_config->libs->$lib;

            if(!file_exists($file)) Util::quit(404);
            Util::download($file);
        });

        Router::route('/lib/min/:lib', function($request) {
            return $request;
        });

        Router::route('/lib/dl/min/:lib', function($request) {
            return $request;
        });
    }
}
