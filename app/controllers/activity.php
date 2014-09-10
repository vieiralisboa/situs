<?php

/**
 * Activity
 * Activity logs
 */
class Activity_Controller {

    public function get() {

    	$file = dirname(dirname(__FILE__))."/config.json";

        #$this->stop(getcwd());// "/public/"

        if(!file_exists($file)) {
            return array();
        }

        $config = json_decode(file_get_contents($file));
        return Activity::logs($config->activity);
    }
}
