<?php
/**
 * PHP 5.2
 */
class Closure_compiler_Controller {
    public function get($request) {
        return "The Closure Compiler API does not respond to GET requests.";
    }

    //POST /upload/file
    public function post($request) {
        if(file_exists(Router::$controller_config->jshrink))
            require Router::$controller_config->jshrink;
        else return null;//$request;

        // path to the upload folder
        $path = Router::$controller_config->uploads;

        /*
         * Compile posted input
         * Saves minified code to file
         */
        if( $request->uri[1] == 'compile') {
            
            // posted code
            $js = file_get_contents("php://input");
            
            // path to output file
            $out = "$path/min-out.js";

            // JShrink posted data
            #$minifiedCode = JShrink\Minifier::minify($postdata);
            // Disable YUI style comment preservation.
            $minifiedCode = Minifier::minify($js, array('flaggedComments' => false));
            
            // save minified code
            file_put_contents($out, $minifiedCode);

            return array (
                'file' => 'out.js',
                //'request' => $request,
                //'filename' => $filename,
                'input' => $js,
                'output' => file_get_contents($out),
                //'command' => $command,
                'out' =>$out,
                'result'=>$minifiedCode
            );
        }
        
        /*
         * Compile from file
         */
        // 
        $name = isset( $request->uri[1] ) ? $request->uri[1] : $_FILES['file']['name'];

        // uploaded file is not a .js file
        if(!preg_match('/\.js$/', $name)) return $_FILES;
        
        // complete path to upload file
        $filename = "$path/$name";

        // remove existing file with same name to avoid false upload success
        if(file_exists($filename)) unlink($filename);

        // save uploaded file
        $success = Util::upload($filename);
        if(!$success) return "File Upload Failed";
        #else return "File Upload Successful";

        // verify if file was uploaded successfuly
        if(file_exists($filename)) {
            
            $js = file_get_contents($filename);
            
            $out = "$path/min-$name";
            
            // JShrink posted data
            #$minifiedCode = Minifier::minify($js);
            $minifiedCode = Minifier::minify($js, array('flaggedComments' => false));

            // save minified code
            file_put_contents($out, $minifiedCode);

            return array (
                'file' => $name,
                'filename' => $filename,
                'input' => $js,
                'output' => file_get_contents($out),
                'command' => $command,
                'out' => $out,
                'result' => $minifiedCode
            );
        }        
        
        return "file upload failed";
    }

    public function options($request){
        return null;
    }
/*
    function __construct(){
        $json = file_get_contents(dirname(__FILE__)."/closure_compiler.json");
        $this->config = json_decode($json);
    }//*/
}