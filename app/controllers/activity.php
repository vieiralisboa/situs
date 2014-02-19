<?php

/**
 * Activity
 * Activity logs
 */
class Activity_Controller {

    public function get() {
        $filename = "../storage/activity.json";
        $activity = (object) array();

        if(file_exists($filename)) {
            $activity = json_decode((file_get_contents($filename)));
        }

        //

        return $activity;
    }
}
