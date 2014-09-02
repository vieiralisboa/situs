<?php

/**
 * Closer-Compiler controller
 */
class Closure_compiler_Controller {

    public function get($request) {
        Router::route('/closure_compiler', function($request) {
            header('Location: http://xn--stio-vpa.pt/#Minify');
            exit;
        });

        Router::route('/closure_compiler/download/:file', function($request) {
            $path = Router::$controller_config->uploads;
            $file = utf8_decode($path."/".$request->data['file']);

            if(!file_exists($file)) Util::quit(404);
            return Util::download($file);
        });
    }

    //POST /upload/file
    public function post($request) {

        if(Router::$controller_config->minifier)
            return minifier($request, Router::$controller_config);

        #return $_FILES;
        #return $request;
        $java = Router::$controller_config->java;//"java.exe";
        $compiler = Router::$controller_config->compiler;

        //---------------------------------------------------------------------
        // Compile from input
        //---------------------------------------------------------------------
        if( $request->uri[1] == 'compile') {
            $postdata = file_get_contents("php://input");
            $path = Router::$controller_config->uploads;
            $in = "$path/in.js";
            $out = "$path/min-out.js";
            $command = "$java -jar $compiler --js=$in --js_output_file=$out";

            file_put_contents($in, $postdata);

            if(file_exists($in)){
                $output = '';
                exec($command, $output);
            }

            return array (
                'file' => 'out.js',
                //'filename' => $filename,
                'input' => file_get_contents($in),
                'output' => file_get_contents($out),
                //'command' => $command,
                //'out' =>$out,
                'result'=>$output
            );

            // upload failed
            return "file upload failed";
        }

        //---------------------------------------------------------------------
        // Compile from file
        //---------------------------------------------------------------------

        // folder to upload file
        $path = Router::$controller_config->uploads;
        $name = isset( $request->uri[1] ) ? $request->uri[1] : $_FILES['file']['name'];

        // uploaded file is not a .js file
        if(!preg_match('/\.js$/', $name)) return $_FILES;

        // complete path to upload file
        $filename = "$path/$name";
        #return $filename;

        // remove any existing file with same name to avoid false upload success
        if(file_exists($filename)) unlink($filename);

        // upload file
        $success = Util::upload($filename);

        // upload successful
        if(file_exists("$path/$name")) {
            $in = "$path/$name";
            $out = "$path/min-$name";
            $command = "$java -jar $compiler --js=$in --js_output_file=$out";

            if(file_exists($in)){
                $output = '';
                exec($command, $output);
            }

            return array (
                'file' => $name,
                'filename' => $filename,
                'input' => file_get_contents("$path/$name"),
                'output' => file_get_contents($out),
                'command' => $command,
                'out' =>$out,
                'result'=>$output
            );
        }

        // upload failed
        return "file upload failed";
    }

    public function options($request){
        return true;
    }
}

function minifier($request, $config){
    if(file_exists($config->jshrink)) require $config->jshrink;
    else return null;//$request;

    // path to the upload folder
    $path = $config->uploads;

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
}
