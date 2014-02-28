<?php

/**
 * Activity
 * Activity logs
 */
class Activity_Controller {
    public function get() {
        return Activity::logs();
    }
}
