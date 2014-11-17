<?php

require_once "recursos/resource.php";

class Recursos_Controller
{
    public function get($request)
    {
        $config = Router::$controller_config;

        // special cases
        switch(strtolower($request->uri[1])) {
            // SQL query
            case "query":
                if(isset($request->uri[2])) {
                    try {
                       return Resource::db($config)->query(urldecode($request->uri[2]));
                    } catch(PDOException $ex) {
                        return $ex->getMessage();
                    }
                }
                return null;

            default:;
        }

        Router::route('/recursos', function($request) { 
            return ['/recursos/query/:query',
                '/recursos/recurso/:id',
                '/recursos/recursivo/:id',
                '/recursos/rendimento/:id',
                '/recursos/:table/:id',
                '/recursos/:table'];
        });

        Router::route('/recursos/recurso/:id', function($request) {
            $config = Router::$controller_config;

            $db = Resource::db($config);
            try {
               $recurso = $db->recurso($request->data['id']);
            }
            catch(PDOException $ex) {
                return $ex->getMessage();
            }

            if(!count($recurso)) return null;
            $recurso = $recurso[0];

            if($recurso['type_code'] == "RCO") {
                $recurso['price'] = $db->composto($recurso['id']);
            }

            $recurso['supplier_id'] = intval($recurso['supplier_id']);
            $recurso['id'] = intval($recurso['id']);
            $recurso['price'] = floatval($recurso['price']);

            return $recurso;
        });

        Router::route('/recursos/recursivo/:id', function($request) {
            $config = Router::$controller_config;
            $db = Resource::db($config);
            return$db->recursivo($db->rendimento($request->data['id']));
        });

        Router::route('/recursos/rendimento/:id', function($request) {
            $config = Router::$controller_config;
            return Resource::db($config)->rendimento($request->data['id']);
        });

        Router::route('/recursos/:table/:id', function($request) {
            $table = strtoupper($request->data['table']);
            $id = $request->data['id'];
            $config = Router::$controller_config;
            return Resource::db($config)->query("SELECT * FROM $table WHERE id = $id;")[0];
        });

        Router::route('/recursos/:table', function($request) {
            $config = Router::$controller_config;
            try {
               return Resource::db($config)->table(strtoupper($request->data['table']));
            }
            catch(PDOException $ex) {
                return $ex->getMessage();
            }
        });

        return null;
    }
}
