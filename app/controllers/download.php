<?php
/**
 * Download
 */
class Download_Controller {
    
    // GET /download/file
    public function get($request) {
        
        $path = dirname(dirname(__FILE__));
        $file = $request->uri[1];
        $filename = "$path\\uploads\\$file";
        
		return Util::download($filename);
    }
}