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
            $HTDOCS = "/shares/jlisboa/WD SmartWare.swstor/ULTRABOOK/Volume.7d313caf.1c93.4c09.838c.44ab9c4ba2a0/htdocs";
            $file = $HTDOCS."/sites/ze/js/bar/templates/".$request->data['template'];
            if(!file_exists($file)) Util::quit(404);
            return Util::serve($file);
        });

        Router::route('/docs/bar/css/:sheet', function($request) {
            $HTDOCS = "/shares/jlisboa/WD SmartWare.swstor/ULTRABOOK/Volume.7d313caf.1c93.4c09.838c.44ab9c4ba2a0/htdocs";
            $file = $HTDOCS."/sites/ze/js/bar/css/".$request->data['sheet'];
            if(!file_exists($file)) Util::quit(404);
            return Util::download($file);
        });

        Router::route('/docs/bar/:file', function($request) {
            $HTDOCS = "/shares/jlisboa/WD SmartWare.swstor/ULTRABOOK/Volume.7d313caf.1c93.4c09.838c.44ab9c4ba2a0/htdocs";
            $file = $HTDOCS."/sites/ze/js/bar/js/".$request->data['file'];
            if(!file_exists($file)) Util::quit(404);
            return Util::download($file);
        });

        Router::route('/docs/uploader/:file', function($request) {
            $HTDOCS = "/shares/jlisboa/WD SmartWare.swstor/ULTRABOOK/Volume.7d313caf.1c93.4c09.838c.44ab9c4ba2a0/htdocs";
            $file = $HTDOCS."/sites/ze/public/upload/".$request->data['file'];
            if(!file_exists($file)) Util::quit(404);
            return Util::download($file);
        });

        Router::route('/docs/:file', function() {
            //return $request->data['file'];
            Util::quit(404);
        });
    }
}
