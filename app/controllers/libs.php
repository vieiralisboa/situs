<?php

class Libs_Controller {
    public function get($request) {
        //return $request;

        $file = get_js($request);

        if(!$file) Util::quit(404);
        return Util::download($file);// ONLY JAVASCRIPT FILES!
    }
}

function get_js($request){
    $uri = preg_replace("/\?[\.\w=\-\&]*$/", "", $_SERVER['REQUEST_URI']);

    // file is not .js or .json
    if(!preg_match('/.js(on)?$/i', $uri)) return 0;

    $basename = basename($uri);
    $script = $uri;
    $filename = utf8_decode($script);

    #$LIBS = "/htdocs";
    $HTDOCS = "/shares/jlisboa/WD SmartWare.swstor/ULTRABOOK/Volume.7d313caf.1c93.4c09.838c.44ab9c4ba2a0/htdocs";
    $file = $HTDOCS.$filename;

    // file does not exist
    if(!file_exists($file)) {
        switch($request->uri[1]){
            case "bar":
                $file = $HTDOCS."/sites/ze/js/bar/js/".$request->uri[2];
                if(file_exists($file)) break;
            default: return 0;
        }
    }

    // not a js file
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

    #$temp_file = $LIBS . "/libs/TEMP/" . $basename;
    $temp_file = "/shares/www/tmp/" . $basename;
    file_put_contents($temp_file, $SCRIPT);

    if(file_exists($temp_file)) {
        return $temp_file;
    }
    else return 0;
}

//ultrabook
function minify_js0($in){
    if(!file_exists($in)) return 0;

    $out = dirname(dirname(__FILE__))."\\uploads\\min-".basename($in);
    $command = "java.exe -jar C:\\htdocs\\libs\\closure-compiler\\compiler-latest\\compiler.jar --js=$in --js_output_file=$out";
    $output = "";
    exec($command, $output);

    if(!file_exists($out)) return 0;
    return file_get_contents($out);
}

// mbl
function minify_js($in){
    // verify if file was uploaded successfuly
    if(!file_exists($in)) return 0;

    $jshrink = "/shares/jlisboa/WD SmartWare.swstor/ULTRABOOK/Volume.7d313caf.1c93.4c09.838c.44ab9c4ba2a0/htdocs/libs/JShrink/src/JShrink/Minifier.php";
    if(file_exists($jshrink)) require $jshrink;
    else return 0;

    return Minifier::minify($in, array('flaggedComments' => false));
}
