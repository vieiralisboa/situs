<?php

class __Controller {

    /**
     * GET
     */
    public function get() {
        Router::route('/_', function() {
            if(file_exists(Router::$controller_config->underscore)){
                $_ = Router::$controller_config->underscore;
            }
            else {
                $_ = Router::$controller_config->temp . "underscore-min.js";
                $data = file_get_contents("http://docs.medorc.org/underscore/1.4.2/underscore-min.js");
                file_put_contents($_, $data);
            }

            Util::serve($_);
        });
    }
}
