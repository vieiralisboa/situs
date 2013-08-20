<?php
/**
 * Download
 */
class Closure_compiler_Controller {
    
    public function get($request) {
        return "The Closure Compiler API does not respond to GET requests.";
    }

    //POST /upload/file 
    public function post($request) {
        #return $_FILES;
        #return $request;

        /*
         * Compile input
         */
        if( $request->uri[1] == 'compile') {
            $postdata = file_get_contents("php://input");
            $path = dirname(dirname(__FILE__))."\\uploads";


            $in = "$path\\in.js";
            $out = "$path\\min-out.js";
            file_put_contents("$path\\in.js", $postdata);

            $command = "C:\\htdocs\\libs\\closure-compiler\\compiler-latest\\java.exe -jar C:\\htdocs\\libs\\closure-compiler\\compiler-latest\\compiler.jar --js=$in --js_output_file=$out";
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
        }

        /*
         * Compile from file
         */

        // folder to upload file
        $path = dirname(dirname(__FILE__))."\\uploads";
     
        $name = isset( $request->uri[1] ) ? $request->uri[1] : $_FILES['file']['name'];

        // uploaded file is not a .js file
        if(!preg_match('/\.js$/', $name)) return $_FILES;
        
        // complete path to upload file
        $filename = "$path\\$name";

        // remove any existing file with same name
        // to avoid false upload success
        if(file_exists($filename)) unlink($filename);

        #return $filename;

        // upload file
        $success = Util::upload($filename);

        // verify if file was uploaded successfuly
        if(file_exists("$path\\$name")) {
            $in = "$path\\$name";
            $out = "$path\\min-$name";

            $command = "C:\\htdocs\\libs\\closure-compiler\\compiler-latest\\java.exe -jar C:\\htdocs\\libs\\closure-compiler\\compiler-latest\\compiler.jar --js=$in --js_output_file=$out";
            if(file_exists($in)){
                #echo "\n$in exists\n";
                #echo "\nexeting: $command\n";
                $output = '';
                exec($command, $output);
            }

            return array (
                'file' => $name,
                //'filename' => $filename,
                'input' => file_get_contents("$path\\$name"),
                'output' => file_get_contents($out),
                //'command' => $command,
                //'out' =>$out,
                'result'=>$output
            );
        }        
        
        return "file upload failed";
    }

    public function options($request){
        return null;
    }
}