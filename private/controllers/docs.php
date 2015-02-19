<?php

class Docs_Controller {

    /**
     * GET
     */
    public function get() {

        Router::route('/docs', function() {
            Util::quit(404);
        });

        Router::route('/docs/bar/templates/:template', function($request) {
            $file = Router::$controller_config->htdocs."/sites/ze/public/bar/templates/".$request->data['template'];

            if(!file_exists($file)) Util::quit(404);
            return Util::serve($file);
        });

        Router::route('/docs/bar/css/:sheet', function($request) {
            $file = Router::$controller_config->htdocs."/sites/ze/public/bar/css/".$request->data['sheet'];
            if(!file_exists($file)) Util::quit(404);
            return Util::download($file);
        });

        Router::route('/docs/bar/:file', function($request) {
            $file = Router::$controller_config->htdocs."/sites/ze/public/bar/js/".$request->data['file'];
            if(!file_exists($file)) Util::quit(404);
            return Util::download($file);
        });

        Router::route('/docs/uploader/:file', function($request) {
            $file = Router::$controller_config->htdocs."/sites/ze/public/public/upload/".$request->data['file'];
            if(!file_exists($file)) Util::quit(404);
            return Util::download($file);
        });

        Router::route('/docs/:file', function() {
            //return $request->data['file'];
            Util::quit(404);
        });
    }
}
