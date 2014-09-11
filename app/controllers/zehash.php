<?php

/**
 * ZeHash
 * password hashing
 */
class Zehash_Controller {

    public function get($request) {
        Router::route('/zehash', function($request){
            return ["routes" => [
                    "/zehash/:string",
                    "/zehash/:string/algo/:algo",
                    "/zehash/:string/salt/:salt",
                    "/zehash/:string/algo/:algo/salt/:salt",
                    "/zehash/:string/verify/:hash"],
                "algos"=> Util::$algos];
        });

        Router::route('/zehash/:string', function($request){
            return Util::zehash($request->data['string']);
        });

        Router::route('/zehash/:string/algo/:algo', function($request){
            return Util::zehash($request->data['string'],
                ["algo"=> $request->data['algo']]);
        });

        Router::route('/zehash/:string/salt/:salt', function($request){
            return Util::zehash($request->data['string'],
                ["salt" => $request->data['salt']]);
        });

        Router::route('/zehash/:string/algo/:algo/salt/:salt', function($request){
            return Util::zehash($request->data['string'],
                ["algo"=> $request->data['algo'],
                "salt" => $request->data['salt']]);
        });

        Router::route('/zehash/:string/verify/:hash', function($request){
            //TODO validate
            return Util::zehash_verify($request->data['string'], $request->data['hash']);
        });
    }
}
