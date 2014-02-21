<?php

class Libs_Controller {
    public function get($request) {
        //return $request;

        $file = get_js($request);

        if(!$file) Util::quit(404);
        return Util::download($file);
    }
}

function get_js($request){
    $uri = preg_replace("/\?[\.\w=\-\&]*$/", "", $_SERVER['REQUEST_URI']);

    // file is not .js or .json
    if(!preg_match('/.js(on)?$/i', $uri)) return 0;

    $basename = basename($uri);
    $script = $uri;
    $filename = utf8_decode($script);

    $LIBS = "/htdocs";
    $file = $LIBS.$filename;

    // file does not exist
    if(!file_exists($file)) return 0;

    // json file
    if(!preg_match('/.js$/i', $basename)) return $file;

    $protocol = $_SERVER['HTTPS']? "https" : "http";

    if(preg_match('/minify/i', $_SERVER['QUERY_STRING'])){
        $body = minify_js($file);
    }
    else $body = file_get_contents($file);

    $filesize = filesize($file);

    $SCRIPT = <<<SCRIPT
//SITUS Libs_Controller>>>
(function(FILE){

//JavaScript $file
$body
//SITUS Libs_Controller>>>
})({
    filename: "$basename",
    filesize: $filesize,
    uri: "$filename",
    path: "$file",
    url: "$protocol://{$_SERVER['SERVER_NAME']}{$_SERVER['REQUEST_URI']}"
});

SCRIPT;

    $temp_file = $LIBS . "/libs/TEMP/" . $basename;
    file_put_contents($temp_file, $SCRIPT);

    if(file_exists($temp_file)) {
        return $temp_file;
    }
    else return 0;
}

// minifies javascript
function minify_js($in){
    if(!file_exists($in)) return 0;

    $out = dirname(dirname(__FILE__))."\\uploads\\min-".basename($in);
    $command = "java.exe -jar C:\\htdocs\\libs\\closure-compiler\\compiler-latest\\compiler.jar --js=$in --js_output_file=$out";
    $output = "";
    exec($command, $output);

    if(!file_exists($out)) return 0;
    return file_get_contents($out);
}
