<?php

/**
 * Schema
 */
class Schema_Controller {
    
    public function post($request) {
        $BASE = dirname(dirname(__FILE__));
        $name = $request->input->name;
        $filename = "$BASE/schemas/$name.json";
        if(!file_exists($filename)){
            file_put_contents($filename, json_encode($request->input));
            if(file_exists($filename)) return 1;
        }
        return 0;
    }
}