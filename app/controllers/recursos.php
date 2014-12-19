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


            case "execute":


            case "recurso":

            if(count($request->uri)<5) break;

            $db = Resource::db(Router::$controller_config);

            $data = array(
                ":name" => urldecode($request->uri[2]),
                ":unit" => urldecode($request->uri[3]),
                ":type" => urldecode($request->uri[4])//,
                //":supplier" => $request->data['supplier'],
                //":description" => $request->data['description']
            );
//return $data;

            // query
            $fields = "(NOME, UNIDADE_CODIGO, TIPO_CODIGO)";
            $values = "(:name, :unit, :type)";
            $sql = "INSERT INTO RECURSO $fields VALUES $values";

            $q = $db->db->prepare($sql);
            return $q->execute($data);

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

        Router::route('/recursos/init', function($request){
            $config = Router::$controller_config;
            try {
               $db = Resource::db($config)->query("SELECT USER() AS USER, DATABASE() AS DB")[0];
            }
            catch(PDOException $ex) {
                return $ex->getMessage();
            }

            $Tables = array();
            try {
                $tables = Resource::db($config)->tables("FICHAS");
                foreach($tables as $table)
                    foreach($table as $name => $value)
                        $Tables[] = $value;
            }
            catch(PDOException $ex) {
                return $ex->getMessage();
            }
            $db[$name] = $Tables;

            $db['Tables'] = array();
            try {
               $unidade = Resource::db($config)->table("UNIDADE");
            }
            catch(PDOException $ex) {
                return $ex->getMessage();
            }
            $db['Tables']['UNIDADE'] = $unidade;

            try {
               $tipo = Resource::db($config)->table("TIPO");
            }
            catch(PDOException $ex) {
                return $ex->getMessage();
            }
            $db['Tables']['TIPO'] = $tipo;

            return $db;
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

        Router::route('/recursos/delete/:id', function($request) {
            $db = Resource::db(Router::$controller_config);
            $sql = "DELETE FROM RECURSO WHERE RECURSO_ID = :id_to_delete";
            $query = $db->db->prepare( $sql );
            return $query->execute(array(":id_to_delete" => $request->data['id']));
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

//return $data;

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
