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
                    }
                    catch(PDOException $ex) {
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
                #'/recurso/:id/:name/:unit/:type/:supplier/:description',
                #"/transaction/:data",
                #"/:tabela/delete/:id",
                #"/:tabela/update/:data",
                #"/:tabela/insert/:data",
                '/recursos/:tabela',
                '/recursos/tabelas'];
        });


        Router::route('/recursos/tabelas', function($request) {
            $config = Router::$controller_config;
            try {
               return Resource::db($config)->tables("FICHAS");
            }
            catch(PDOException $ex) {
                return $ex->getMessage();
            }
        });

        if(true) Router::route('/recursos/recurso/:name/:unit/:type', function($request) {
            $db = Resource::db(Router::$controller_config);

            $data = array(
                ":name" => $request->data['name'],
                ":unit" => $request->data['unit'],
                ":type" => $request->data['type']//,
                //":supplier" => $request->data['supplier'],
                //":description" => $request->data['description']
            );

return $data;

            // query
            $fields = "(NOME, UNIDADE_CODIGO, TIPO_CODIGO)";
            $values = "(:name, :unit, :type)";
            $sql = "INSERT INTO RECURSO $fields VALUES $values";

            $q = $db->db->prepare($sql);
            return $q->execute($data);
            //$q->execute(array(':author'=>$author, ':title'=>$title));
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

            if($recurso['TIPO_CODIGO'] == "COMP") {
                $recurso['RECURSO_PRECO'] = $db->composto($recurso['RECURSO_ID']);
            }

            //TODO verificar se é realmente necessário intval(), floatval()
            //$recurso['supplier_id'] = intval($recurso['supplier_id']);
            $recurso['RECURSO_ID'] = intval($recurso['RECURSO_ID']);
            $recurso['RECURSO_PRECO'] = floatval($recurso['RECURSO_PRECO']);

            return $recurso;
        });

        Router::route('/recursos/recursivo/:id', function($request) {
            $config = Router::$controller_config;
            $db = Resource::db($config);

            return $db->recursivo( $db->rendimento($request->data['id']) );
        });

        Router::route('/recursos/rendimento/:id', function($request) {
            $config = Router::$controller_config;
            return Resource::db($config)->rendimento($request->data['id']);
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

        return false;
    }
}
