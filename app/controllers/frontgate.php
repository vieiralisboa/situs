<?php

/**
 * Frontgate Controller
 */
class Frontgate_Controller {

    /**
     * GET
     */
    public function get() {
        Router::route('/frontgate', function() {
            $path = Router::$controller_config->frontgate;
            $file = utf8_decode($path."js/frontgate.js");
            if(!file_exists($file)) Util::quit(404);
            return Util::serve($file);
        });

        Router::route('/frontgate/css', function($request) {
            $path = Router::$controller_config->frontgate;
            $file = utf8_decode($path."css/frontgate.css");
            if(!file_exists($file)) Util::quit(404);
            return Util::serve($file);
        });

        // frontgate/and/:module
        Router::route('/frontgate/and/:module', function($request) {
            $path = Router::$controller_config->frontgate;
            $file = $path."js/frontgate.js";
            if(!file_exists($file)) Util::quit(404);
            $body = "";

            switch($request->data['module']){
                case "situs":
                    $situs = $path."js/frontgate.situs.js";
                    if(!file_exists($situs)) break;
                    $require = Router::$controller_config->require;
                    $docs = Router::$controller_config->docs;
                    foreach ($require->situs as $script) {
                        $script = $docs.$script;
                        $body .= file_get_contents($script);
                    }
                    $body .= file_get_contents($situs);

                case "router":
                    $router = $path."js/frontgate.router.js";
                    if(!file_exists($router)) break;
                    $body = file_get_contents($router).$body;
                    break;

                default:
                    Util::quit(404);
            }

            $frontgate = file_get_contents($file);
            $temp = Router::$controller_config->temp;
            $temp .= "frontgate.and.{$request->data['module']}.js";
            $protocol = $_SERVER['HTTPS']? "https" : "http";

$SCRIPT = <<<SCRIPT
//JavaScript
//Frontgate_Controller>>>
(function(FILE){
//script $temp
$frontgate
$body
//Frontgate_Controller>>>
})({
    frontgate: "$file",
    router: "$router",
    situs: "$situs",
    url: "$protocol://{$_SERVER['SERVER_NAME']}{$_SERVER['REQUEST_URI']}"
});
SCRIPT;

            file_put_contents($temp, $SCRIPT);
            if(!file_exists($temp)) Util::quit(404);
            return Util::serve($temp);
        });

        Router::route('/frontgate/config', function($request) {
            return Router::$controller_config;
        });

        Router::route('/frontgate/:module', function($request) {
            $path = Router::$controller_config->frontgate;
            $file = utf8_decode($path."js/frontgate.{$request->data['module']}.js");
            if(!file_exists($file)) Util::quit(404);
            return Util::serve($file);
        });
    }

    /**
     * POST
     */
    public function post() {
        Util::quit(501);
    }

    /**
     * UPDATE
     */
    public function put(){
        Util::quit(501);
    }

    /**
     * DELETE
     */
    public function delete($request){
        Util::quit(501);
    }

    public function options(){
        Util::quit(204);
    }
}
