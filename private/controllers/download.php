<?php
/**
 * Download from uploads folder
 */
class Download_Controller {
    
    // GET /download/file
    public function get($request) {
        
        #$path = dirname(dirname(__FILE__));// ultrabook
    	$path = "/shares/www/uploads/";// myBookLive

        $file = $request->uri[1];
		#$filename = "$path\\uploads\\$file";// ultrabook
        $filename = $path.$file;// linux
        
		return Util::download($filename);
    }
}
