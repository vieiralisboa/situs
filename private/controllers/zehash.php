<?php

/**
 * ZeHash
 * password hashing
 */
class Zehash_Controller {

    public function get($request) {
        Router::route('/zehash', function($request){
            return ["routes" => [
                    "/zehash/:string" => "generates sha1 hashed :string",
                    "/zehash/:string/:algo" => "generates :algo hashed :string",
                    "/zehash/:string/:algo/:cost" => "generates :algo hashed :string with :cost passes",
                    "/zehash/:string/cost/:cost" => "generates sha1 hashed :string with :cost passes",
                    "/zehash/:string/verify/:hash" => "verifies if :hash was generated with :string"],
                "algos"=> hash_algos()];
        });

        Router::route('/zehash/:string', function($request){
            return Util::zehash($request->data['string']);
        });

        Router::route('/zehash/:string/:algo', function($request){
            return Util::zehash($request->data['string'],
                ["algo"=> $request->data['algo']]);
        });

        Router::route('/zehash/:string/cost/:cost', function($request){
            return Util::zehash($request->data['string'],
                ["cost" => $request->data['cost']]);
        });

        Router::route('/zehash/:string/verify/:hash', function($request){
            //TODO validate
            return Util::zehash_verify($request->data['string'], $request->data['hash']);
        });

        Router::route('/zehash/:string/:algo/:cost', function($request){
            return Util::zehash($request->data['string'],
                ["algo"=> $request->data['algo'],
                "cost" => $request->data['cost']]);
        });
    }
}
