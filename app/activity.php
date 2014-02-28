<?php

class Activity
{
    private static $logs;
    private static $root;
    private static $filename;
    private static $log;
    private static $set;
    private static $saved = 0;
    private static $fetched = 0;
    private static $json;

    // gets or sets filename
    public static function filename($filename = false)
    {
        if($filename === false)
            return self::$root . "/logs/" . self::$filename;
        self::$filename = $filename;
        return self::$filename;
    }

    public static function json()
    {
        if(!self::$fetched) self::_fetch();
        return self::$json;
    }

    public static function logs()
    {
        if(!self::$fetched) self::_fetch();
        return self::$logs;
    }

    // logs activity
    public static function log()
    {
        self::_save();// save
        return self::$log;
    }

    // fetches logs
    private function _fetch()
    {
        if(!self::$fetched)
        {
            self::_set();
            $filename = self::filename();
            if(file_exists($filename))
            {
                self::$json = file_get_contents($filename);
                self::$logs = json_decode(self::$json);
            }
            else self::$logs = (object) array();
            self::$fetched = 1;
        }
    }

    private function _save()
    {
        if(!self::$saved)
        {
            if(!self::$fetched) self::_fetch();
            self::_log();
            file_put_contents(self::filename(), json_encode(self::$logs));
            self::$saved = 1;
        }
    }

    // sets attributes
    private function _set(){
        if(self::$set) return 0;
        self::$logs = (object) array();
        self::$root = dirname(dirname(__FILE__));
        if(!self::$filename)
            self::$filename = "activity_".date("Ym").".json";
        return self::$set = 1;
    }

    // sets log
    private function _log()
    {
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
