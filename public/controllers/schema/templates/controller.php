<?php

/**
 * schema.php
 * Situs Controller
 * Saves the schema data in the file {name}.json,
 * stored in the schemas folder on the server.
 * The controller for {name} is created on the first REST request.
 */
class Schema_Controller{
    public function post($request){
        $BASE = dirname(dirname(__FILE__));
        $name = $request->input->name;
        $schema = "$BASE/schemas/$name.json";
        $auth = "$BASE/auth/$name.json";

        if(!file_exists($schema) && !file_exists($auth)){
            file_put_contents($schema,
                json_encode($request->input));

            file_put_contents($auth,
                json_encode(array($request->input->auth)));

            if(file_exists($schema) && file_exists($auth)){
                return 1;
            }
            else return false;
        }

        return true;
    }
}
