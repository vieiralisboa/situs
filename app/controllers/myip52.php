<?php

/**
 * MyIP
 * Remote IP and host name
 */
class Myip52_Controller {
    public function get() {
        $host = array('ip' => $_SERVER['REMOTE_ADDR'],
            'hostname'=> gethostbyaddr($_SERVER['REMOTE_ADDR']));
        return $host;
    }
}