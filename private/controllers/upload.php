<?php

/**
 * Upload
 */
class Upload_Controller {

    //POST /upload/file 
    public function post($request) {

        //validate here

        #$path = dirname(dirname(__FILE__))."\\uploads\\";// ultrabook
        $path = "/shares/www/uploads/";// MBL
        $file = isset($request->uri[1]) ? $request->uri[1] : "";

        return Util::upload($path.$file);
    }

    public function options($request){
        return null;
    }
}
