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
            $path = WWW.Router::$controller_config->ze;
            $file = utf8_decode("$path/js/bar.js");

            if(!file_exists($file)) Util::quit(404);
            return Util::serve($file);
        });

        Router::route('/bar/css', function($request) {
            // full path to file
            $path = WWW.Router::$controller_config->ze;
            $file = utf8_decode("$path/css/bar.css");

            if(!file_exists($file)) Util::quit(404);
            return Util::serve($file);
        });

        Router::route('/bar/:bar/template/:name', function($request) {
            $path = WWW.Router::$controller_config->bars;
            $template = "{$request->data['bar']}/templates/{$request->data['name']}.html";

            $file = utf8_decode("$path/$template");
            if(!file_exists($file)) Util::quit(404);
            return Util::serve($file);
        });

        Router::route('/bar/:bar/css', function($request) {
            // file name
            $filename = "bar.{$request->data['bar']}.css";
            $path = WWW.Router::$controller_config->bars;
            $file = utf8_decode("$path/$filename");

            if(!file_exists($file)) Util::quit(404);
            return Util::serve($file);
        });

        Router::route('/bar/:bar/json', function($request) {
            // file name
            $filename = "bar.{$request->data['bar']}.json";
            $path = WWW.Router::$controller_config->bars;
            $file = utf8_decode("$path/$filename");

            if(!file_exists($file)) Util::quit(404);
            return Util::serve($file);
        });

        Router::route('/bar/config', function($request) {
            return Router::$controller_config;
        });

        Router::route('/bar/:bar', function($request) {
            // file name
            $filename = "bar.{$request->data['bar']}.js";

            // relative script url
            $script = "jquery.bar/js/$filename";

            // full path to file
            $path = WWW.Router::$controller_config->bars;
            $file = utf8_decode("$path/$filename");

            // attach the filename
            #$temp_file = Router::$controller_config->temp . $filename;
            $temp_file = TEMP_FOLDER.$filename;
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
//die($temp_file);
            file_put_contents($temp_file, $SCRIPT);
            if(file_exists($temp_file)) Util::serve($temp_file);//Util::download($temp_file);
            else Util::quit(404);

        });
    }

    /**
     * POST
     */
    public function post()
    {
        Util::quit(501);
    }

    /**
     * UPDATE
     */
    public function put()
    {
        Util::quit(501);
    }

    /**
     * DELETE
     */
    public function delete($request)
    {
        Util::quit(501);
    }

    public function options()
    {
        Util::quit(204);
    }
}
