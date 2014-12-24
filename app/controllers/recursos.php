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

            case "fornecedor":
                if(count($request->uri) < 4) break;
                switch(count($request->uri)) {
                    // fornecedor/:nome/:morada

                    case 4:
                        $db = Resource::db(Router::$controller_config);
                        $data = array(
                            ":nome" => urldecode($request->uri[2]),
                            ":morada" => urldecode($request->uri[3])
                        );
                        // query
                        $fields = "(FORNECEDOR_NOME, FORNECEDOR_MORADA)";
                        $values = "(:nome, :morada)";
                        $sql = "INSERT INTO FORNECEDOR $fields VALUES $values";
                        $q = $db->db->prepare($sql);
                        break;

                    // fornecedor/:id/:nome/:morada
                    case 5:
                        $data = array(
                            urldecode($request->uri[3]),// :name
                            urldecode($request->uri[4]),// :morada
                            intval($request->uri[2])// :id
                        );
                        $db = Resource::db(Router::$controller_config);
                        $sql = "UPDATE FORNECEDOR SET FORNECEDOR_NOME=?, FORNECEDOR_MORADA=? WHERE FORNECEDOR_ID=?";
                        $q = $db->db->prepare($sql);
                        break;

                    default: return false;
                }

                try
                {
                    $result = $q->execute($data);
                }
                catch(PDOException $ex) {
                    return $ex->getMessage();
                }
                return $result;

            case "recurso":
                if(count($request->uri) < 5) break;
                switch(count($request->uri)) {
                    case 5:
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

                    case 6:
                        $values = array(
                            urldecode($request->uri[3]),// name
                            urldecode($request->uri[4]),// unit
                            urldecode($request->uri[5]),// type
                            intval($request->uri[2])// id
                        );

                        $db = Resource::db(Router::$controller_config);
                        $sql = "UPDATE RECURSO SET NOME=?, UNIDADE_CODIGO=?, TIPO_CODIGO=? WHERE RECURSO_ID=?";
                        //return $sql;

                        $q = $db->db->prepare($sql);

                        try
                        {
                            $result = $q->execute($values);
                        }
                        catch(PDOException $ex) {
                            return $ex->getMessage();
                        }

                        return $result;
                    default:;
                }
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

        Router::route('/recursos/precos/:id', function($request) {
            $config = Router::$controller_config;
            $id = $request->data['id'];
            $fields = "PRECO.RECURSO_ID, PRECO.FORNECEDOR_ID, FORNECEDOR_NOME as fornecedor, DATE_FORMAT(PRECO.DATA, '%Y-%m-%d') as DATA, VALOR";
            $tables = "PRECO, FORNECEDOR";
            $restrictions = "PRECO.FORNECEDOR_ID = FORNECEDOR.FORNECEDOR_ID AND RECURSO_ID = $id";
            $query = "SELECT $fields FROM $tables WHERE $restrictions";
            try {
                $precos = Resource::db($config)->query($query);
            }
            catch(PDOException $ex) {
                return $ex->getMessage();
            }
            try {
                $query = "SELECT FORNECEDOR_ID FROM FORNECIMENTO WHERE RECURSO_ID = $id";
                $fornecimento = Resource::db($config)->query($query);
            }
            catch(PDOException $ex) {
                return $ex->getMessage();
            }

            $fornecedor_id = count($fornecimento > 0) ? $fornecimento[0]['FORNECEDOR_ID'] : 0;
            foreach($precos as $i => $preco) {
                if($preco['FORNECEDOR_ID'] == $fornecedor_id) $precos[$i]['fornece'] = true;
                else $precos[$i]['fornece'] = false;
            }

            return $precos;
        });

        Router::route('/recursos/init', function($request) {
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
            $sql = "DELETE FROM RECURSO WHERE RECURSO_ID = :id";
            $query = $db->db->prepare( $sql );

            try {
               $response = $query->execute(array(":id" => $request->data['id']));
            }
            catch(PDOException $ex) {
                return $ex->getMessage();
            }
            return array($response, $request);
            return $response;
        });

        Router::route('/recursos/fornecimento/delete/:rid', function($request) {
            $db = Resource::db(Router::$controller_config);
            $sql = "DELETE FROM FORNECIMENTO WHERE RECURSO_ID = :rid";
            $query = $db->db->prepare($sql);
            $data = array(":rid" => intval($request->data['rid']));
            try {
               $response = $query->execute($data);
            }
            catch(PDOException $ex) {
                return $ex->getMessage();
            }
            return array($response, $request);
        });

        Router::route('/recursos/fornecimento/:fid/:rid', function($request) {
            $db = Resource::db(Router::$controller_config);

            $data = array(
                ":fid" => intval($request->data['fid']),
                ":rid" => intval($request->data['rid'])
            );

            // query
            $fields = "(FORNECEDOR_ID, RECURSO_ID)";
            $values = "(:fid, :rid)";
            $sql = "INSERT INTO FORNECIMENTO $fields VALUES $values";

            $q = $db->db->prepare($sql);
            
            try {
               $response = $q->execute($data);
            }
            catch(PDOException $ex) {
                return $ex->getMessage();
            }
            return $response;
        });

        Router::route('/recursos/preco/delete/:fid/:rid', function($request) {
            $db = Resource::db(Router::$controller_config);
            $sql = "DELETE FROM PRECO WHERE RECURSO_ID = :rid AND FORNECEDOR_ID = :fid";
            $data = array(":rid" => intval($request->data['rid']), ":fid" => intval($request->data['fid']));
            $query = $db->db->prepare($sql);
            try {
               $response = $query->execute($data);
            }
            catch(PDOException $ex) {
                return $ex->getMessage();
            }
            return array($response, $request);
        });

        Router::route('/recursos/preco/:rid/:fid/:val/:data', function($request) {
            $db = Resource::db(Router::$controller_config);

            $data = array(
                ":rid" => intval($request->data['rid']),
                ":fid" => intval($request->data['fid']),
                ":val" => floatval($request->data['val']),
                ":date" => $request->data['data']
            );

            //*/ query
            $fields = "(FORNECEDOR_ID, RECURSO_ID, VALOR, DATA)";
            $values = "(:fid, :rid, :val, :date)";
            $sql = "INSERT INTO PRECO $fields VALUES $values";

            $q = $db->db->prepare($sql);
            
            try {
               $response = $q->execute($data);
            }
            catch(PDOException $ex) {
                return $ex->getMessage();
            }
            return $response;
            //*/
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
