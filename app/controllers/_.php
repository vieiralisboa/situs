<?php

class __Controller {

    /**
     * GET
     */
    public function get() {
        Router::route('/_', function() {
            $_ = "c:\\temp\\underscore-min.js";
            $data = file_get_contents("http://docs.medorc.org/underscore/1.4.2/underscore-min.js");
            file_put_contents($_, $data);
            Util::serve($_);
        });
    }
}
