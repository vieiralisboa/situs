<?php

/**
 * Bar Controller
 */
class Bar_Controller {

    /**
     * GET
     */
    public function get() {
        //return $_SERVER;

        Router::route('/bar', function() {
            Util::quit(404);
        });

        Router::route('/bar/:bar', function($request) {
            $json = file_get_contents(dirname(__FILE__)."/bar.conf.json");
            $config = json_decode($json);
            // file name
            $filename = "bar.{$request->data['bar']}.js";

            // relative script url
            $script = "jquery.bar/js/$filename";
            // full path to file
            $file = utf8_decode($config->htdocs . $config->bar . "js/$filename");

            // attach the filename
            $temp_file = $config->temp . $filename;
            if(file_exists($file)) {
                $body = file_get_contents($file);
                $json = file_get_contents($file."on");
                if($json) $json = json_encode(json_decode($json));
                else $json = json_encode(null);
            }
            else Util::quit(404);

            $protocol = $_SERVER['HTTPS']? "https" : "http";

$SCRIPT = <<<SCRIPT
//Situs_Controller>>>
(function(FILE){

//JavaScript $file
$body
//Situs_Controller>>>
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
            if(file_exists($temp_file)) return Util::download($temp_file);
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
