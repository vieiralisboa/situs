<?php

/**
 * Hash
 * String hashing
 */
class Hash_Controller {

    public function get($request) {

        Router::route('/hash', function($request){
            return [
                "routes" => [
                    "/hash/:string",
                    "/hash/:string/:algo",
                    "/hash/:string/:algo/:cost"],
                "algos"=>hash_algos()

            ];//$request;
        });

        Router::route('/hash/:string', function($request){
            $algo = "sha1";
            return array(
                "string" => $request->data['string'],
                "algorithm" => $algo,
                "hash" => hash($algo, $request->data['string'])
            );
        });

        Router::route('/hash/:string/:algo', function($request){
            $algo = null;

            foreach(hash_algos() as $mode){
                if($request->data['algo'] == $mode) {
                    $algo = $mode;
                    break;
                }
            }

            $algo = $algo ? $algo : "sha1";

            return array(
                "string" => $request->data['string'],
                "algorithm" => $algo,
                "hash" => hash($algo, $request->data['string'])
            );
        });

        Router::route('/hash/:string/:algo/:cost', function($request){
            $algo = null;
            $cost = 0;

            foreach(hash_algos() as $mode){
                if($request->data['algo'] == $mode) {
                    $algo = $mode;
                    break;
                }
            }

            $algo = $algo ? $algo : "sha1";
            $cost = (int) $request->data['cost'];
            if($cost < 1) $cost = 1;
            if($cost > 65536) $cost = 65536;

            $hash = hash($algo, $request->data['string']);
            for($i=1; $i<$cost; $i++){
                $hash = hash($algo, $hash);
            }

            return array(
                "string" => $request->data['string'],
                "algorithm" => $algo,
                "passes" => $cost,
                "hash" => $hash
            );
        });
    }
}
