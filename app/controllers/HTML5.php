<?php

/**
 * HTML5
 */
class HTML5_Controller {

    // GET
    public function get($request) {
         Router::route('/HTML5/video/popup', function($request) {
            $file = Router::$controller_config->video->popup;

            if(!file_exists($file)) Util::quit(404);
            return Util::serve($file);
        });
    }
}
