<?php

class Activity {
    private static $logs;
    private static $root;
    private static $filename;
    private static $log;
    private static $set;
    private static $saved = 0;
    private static $fetched = 0;
    private static $json;

    // gets or sets filename
    public static function filename($filename = false){
        if($filename === false)
            return self::$root . self::$filename;
        self::$filename = $filename;
        return self::$filename;
    }

    public static function json($config){
        if(!self::$fetched) self::_fetch($config);
        return self::$json;
    }

    public static function logs($config){
        if(!self::$fetched) self::_fetch($config);
        return self::$logs;
    }

    // logs activity
    public static function log($config){
        self::_save($config);// save
        return self::$log;
    }

    // fetches logs
    private static function _fetch($config){
        if(!self::$fetched){
            self::_set($config);
            $filename = self::filename();
            if(file_exists($filename)){
                self::$json = file_get_contents($filename);
                self::$logs = json_decode(self::$json);
            }
            else self::$logs = (object) array();
            self::$fetched = 1;
        }
    }

    private static function _save($config){
        if(!self::$saved){
            if(!self::$fetched) self::_fetch($config);
            self::_log();
            file_put_contents(self::filename(), json_encode(self::$logs));
            self::$saved = 1;
        }
    }

    // sets attributes
    private static function _set($config){
        if(self::$set || !is_object($config)) return 0;
        self::$logs = (object) array();
        if(!self::$filename){
            $_filename = isset($config->filename) ? $config->filename : "situs_activity";
            $_date = isset($config->date) ? $config->date : "Ym";
            $_folder = isset($config->folder) ? $config->folder : dirname(dirname(__FILE__))."/";

            self::$root = $config->folder;
            self::$filename = $_filename."_".date($config->date).".json";
        }
        return self::$set = 1;
    }

    // sets|returns log entry
    private static function _log(){
        $agent = $_SERVER['HTTP_USER_AGENT'];
        $address = $_SERVER['REMOTE_ADDR'];
        $request = $_SERVER['REQUEST_URI'];
        $today = date("Ymd");

        self::$log = (object) array(
            'agent' => $agent,
            'address' => $address,
            'request' => $request,
            'date' => $today
        );

        if(!isset(self::$logs->$address))
            self::$logs->$address = (object) array();
        if(!isset(self::$logs->$address->$agent))
            self::$logs->$address->$agent = (object) array();
        if(!isset(self::$logs->$address->$agent->$today))
            self::$logs->$address->$agent->$today = (object) array();
        if(!isset(self::$logs->$address->$agent->$today->$request))
            self::$logs->$address->$agent->$today->$request = 0;
        self::$logs->$address->$agent->$today->$request += 1;

        return self::$log;
    }
}
