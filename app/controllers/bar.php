<?php

/**
 * Bar Controller
 * TODO extend config
 */
class Bar_Controller {

    /**
     * GET
     */
    public function get() {
        Router::route('/bar', function() {
            // full path to file
            $conf = Router::$controller_config;
            $file = utf8_decode($conf->htdocs.$conf->bar."js/bar.js");

            if(!file_exists($file)) Util::quit(404);
            return Util::serve($file);
        });

        Router::route('/bar/css', function($request) {
            // full path to file
            $conf = Router::$controller_config;
            $file = utf8_decode($conf->htdocs.$conf->bar."css/bar.css");

            if(!file_exists($file)) Util::quit(404);
            return Util::serve($file);
        });

        Router::route('/bar/templates/:template', function($request) {
            $conf = Router::$controller_config;
            $path = $conf->htdocs.$conf->bar."templates/";
            $file = utf8_decode($path.$request->data['template']);
            if(!file_exists($file)) Util::quit(404);
            return Util::serve($file);
        });

        Router::route('/bar/config', function($request) {
            return Router::$controller_config;
        });

        Router::route('/bar/css/:bar', function($request) {
            // file name
            $filename = "bar.{$request->data['bar']}.css";

            // full path to file
            $conf = Router::$controller_config;
            $file = utf8_decode($conf->htdocs.$conf->bar."css/$filename");

            if(!file_exists($file)) Util::quit(404);
            return Util::serve($file);
        });

        Router::route('/bar/:bar', function($request) {
            // file name
            $filename = "bar.{$request->data['bar']}.js";

            // relative script url
            $script = "jquery.bar/js/$filename";

            // full path to file
            $conf = Router::$controller_config;
            $file = utf8_decode($conf->htdocs.$conf->bar."js/$filename");

            // attach the filename
            $temp_file = $conf->temp . $filename;
            if(file_exists($file)) {
                $body = file_get_contents($file);
                $json = file_get_contents($file."on");
                if($json) $json = json_encode(json_decode($json));
                else $json = json_encode(null);
            }
            else Util::quit(404);

            $protocol = $_SERVER['HTTPS']? "https" : "http";

$SCRIPT = <<<SCRIPT
//JavaScript
//Bar_Controller>>>
(function(FILE){

//script $file
$body
//Bar_Controller>>>
})({
    bar: "{$request->data['bar']}",
    name: "{$request->data['bar']}",
    filename: "$filename",
    script: "$script",
    path: "$file",
    url: "$protocol://{$_SERVER['SERVER_NAME']}{$_SERVER['REQUEST_URI']}",
    json: '$json'
});
SCRIPT;

            file_put_contents($temp_file, $SCRIPT);
            if(file_exists($temp_file)) Util::serve($temp_file);//Util::download($temp_file);
            else Util::quit(404);

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
