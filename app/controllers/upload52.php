<?php
/**
 * Download
 */
class Upload52_Controller {
    
    //POST /upload/file 
    public function post($request) {

        //validate here
        
        $path = dirname(dirname(__FILE__))."\\uploads";
		$file = isset($request->uri[1]) ? $request->uri[1] : "";
        
        return Util::upload("$path\\$file");
    }

    public function options($request){
        return null;
    }
}