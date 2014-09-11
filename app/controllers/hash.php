<?php

/**
 * Hash
 * String and password hashing
 */
class Hash_Controller {

    public function get($request) {

        Router::route('/hash', function($request){
            //return $request;
        });

        Router::route('/hash/password/:password', function($request){
            //TODO validate
            return Util::zehash($request->data['password'], $_SERVER['REMOTE_ADDR']);
        });

        Router::route('/hash/password/:password/:salt', function($request){
            //TODO validate
            return Util::zehash($request->data['password'], $request->data['salt']);
        });

        Router::route('/hash/:algo/:string', function($request){
            $algo = null;

            foreach(["md5", "sha1", "sha256", "sha512", "ripemd128"] as $mode){
                if($request->data['algo'] == $mode) {
                    $algo = $mode;
                    break;
                }
            }

            $algo = $algo ? $algo : "md5";

            return array(
                "string" => $request->data['string'],
                "algorithm" => $algo,
                "hash" => hash($algo, $request->data['string'])
            );
        });

        Router::route('/hash/verify/:password/:hash', function($request){
            //TODO validate
            return Util::zehash_verify($request->data['password'], $request->data['hash']);
        });
    }
}
