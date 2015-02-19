<?php
/**
 * Situs - A PHP Framework
 *
 * @package  Situs
 * @version  0.0.0
 * @author   Jose Vieira Lisboa <jose.vieira.lisboa@gmail.com>
 * @link     http://situs.pt
 */

/**
 * RESTful Controller
 */
class Fichas_Controller {

    /**
     * GET
     */
    public function get($request){

        require dirname(__FILE__)."/recursos/recursos.database.php";

        Router::route('/fichas', function($request){
            return [
                'FICHAS DE RENDIMENTO',
                '/fichas/:tabela',
                '/fichas/:tabela/:id',
                '/fichas/:tabela/delete/:id',
                '/fichas/recurso/:id/:name/:price/:unit/:type/:fid',
                '/fichas/rendimento/:id/:cid/:rid/:factor'
            ];
        });

        Router::route('/fichas/recursos/tipo/:tipo', function($request){
            $sql = "SELECT * FROM recursos WHERE R_TIPO='{$request->data['tipo']}';";
            return Recursos::query($sql);
        });

        Router::route('/fichas/:tabela', function($request){
            return Recursos::all($request->data['tabela']);
        });

        Router::route('/fichas/:tabela/:id', function($request){
            $id = $request->data['id'];
            $recurso = Recursos::find($id, $request->data['tabela']);

            if($request->data['tabela'] == "recursos" && $recurso['R_TIPO'] == "COMPOSTO"){
                $fields = "recursos.R_ID, R_NOME, C_FATOR, R_UNIDADE, R_PRECO, R_TIPO, F_ID, R_DATA";
                $tables = "rendimentos, recursos";
                $where = "recursos.R_ID = rendimentos.R_ID AND rendimentos.RC_ID = $id";
                $sql = "SELECT $fields FROM $tables WHERE $where";
                $recursos = Recursos::query($sql);

                // CALCULAR PREÇO DO RECURSO COMPOSTO
                 $preco = 0;
                foreach($recursos as $r){
                    $preco += $r['C_FATOR']*$r['R_PRECO'];
                }

                // ATUALIZAR O PREÇO DO RECURSO COMPOSTO
                $res = null;
                if($recurso['R_PRECO'] != $preco){
                    $data = date("Y-m-d H:m:s");
                    $pares = "R_PRECO = $preco, R_DATA = '$data'";
                    $sql = "UPDATE recursos SET $pares WHERE R_ID=$id";
                    //return $sql;
                    Recursos::query($sql);

                    $recurso = Recursos::find($id, $request->data['tabela']);
                    $res = verifyArray(array('R_PRECO' => $preco), $recurso);
                }

                return array(
                    "res" => $res,
                    "recurso_composto" => $recurso,
                    "recursos" => $recursos
                );
            }
            else return $recurso;
        });

        // rendimento
        Router::route('/fichas/rendimento/:id/:cid/:rid/:factor', function($request) {
            $id = (int) $request->data['id'];
            $cid = (int) $request->data['cid'];
            $rid = (int) $request->data['rid'];
            $factor = (float) $request->data['factor'];
            $date = date("Y-m-d H:m:s");
            $res = array();

            // VALIDAR R_ID
            $sql = "SELECT * FROM recursos WHERE R_ID = $cid";
            $res = Recursos::query($sql);
            if(!count($res)) return "RECURSO $cid NAO EXISTE";
            else if($res[0]['R_TIPO'] != "COMPOSTO") return "RECURSO NAO COMPOSTO";
            $sql = "SELECT * FROM recursos WHERE R_ID = $rid";
            if(!count(Recursos::query($sql))) return "NAO TEM RECURSOS";

            // para inserir
            if($id === 0) {
                $where = "RC_ID=$cid AND R_ID=$rid";
                $sql = "SELECT COUNT(*) AS N FROM rendimentos WHERE $where";
                $res = Recursos::query($sql);
                if($res[0]['N']>0) return "RENDIMENTO JA EXISTE";

                $values = "($cid, $rid, $factor, '$date')";
                $fields = "(RC_ID, R_ID, C_FATOR, C_DATA)";
                $sql = "INSERT INTO rendimentos $fields VALUES $values";
            }
            // para actializar
            else {
                // $cid must exist
                $sql = "SELECT * FROM rendimentos WHERE C_ID = $id";
                if(!count($res = Recursos::query($sql))) return "RENDIMENTO $id NAO EXISTE";

                // verify if it needs updating
                if(verifyArray(array('R_ID'=>$rid,'C_FATOR'=>$factor), $res[0]))
                    return "ALREADY UPDATED";

                $pairs = "R_ID=$rid, C_FATOR=$factor, C_DATA='$date'";
                $sql = "UPDATE rendimentos SET $pairs WHERE C_ID=$id";
            }

            Recursos::query($sql);

            $sql = "SELECT * FROM rendimentos WHERE C_ID = $id";
            return $res = Recursos::query($sql);
            if(verifyArray(array('R_ID'=>$rid,'C_FATOR'=>$factor), $res[0])) return "OK";
            return false;
        });

        // recurso
        Router::route('/fichas/recurso/:id/:name/:price/:unit/:type/:fid', function($request) {
            //return $request;

            $id = (int) $request->data['id'];
            $nome = $request->data['name'];
            $price = (float) $request->data['price'];
            $unit = $request->data['unit'];
            $type = $request->data['type'];
            $date = date("Y-m-d H:m:s");
            $fid = (int) $request->data['fid'];

            if(!$id) {// inserir
                $fields = "(R_NOME, R_PRECO, R_UNIDADE, R_TIPO, R_DATA, F_ID)";
                $values = "('$nome', $price, '$unit', '$type', '$date', $fid)";
                $sql = "INSERT INTO recursos $fields VALUES $values";
            }
            else {// atualizar
                $sql = "SELECT * FROM recursos WHERE R_ID = $id";
                if(!count($res = Recursos::query($sql))) // $cid must exist
                    return false;

                if(verifyArray(array(
                    "R_NOME" => $nome,
                    "R_PRECO"=> $price,
                    "R_TIPO"=> $type,
                    "F_ID" => $fid,
                    "R_UNIDADE" => $unit
                ), $res[0])) return "ALREADY UPDATED";

                // actualizar recursos compostos
                #if($price != $res[0]["R_PRECO"]) Recursos::compostos($res[0]);

                $pairs = "R_NOME='$nome', R_PRECO=$price, R_UNIDADE='$unit', R_TIPO='$type', R_DATA='$date', F_ID=$fid";
                $sql = "UPDATE recursos SET $pairs WHERE R_ID=$id;";
            }

            return Recursos::query($sql);

        });

        Router::route('/fichas/:tabela/delete/:id', function($request) {
            // delets a record by its id in the database
            return Recursos::delete($request->data['tabela'],
                array(($request->data['tabela'] == "recursos"?"R_ID":"C_ID"), $request->data['id']));
        });
    }
}

function verifyArray($before, $after){
    $ok = true;
    foreach($before as $name => $value)
        if($after[$name] != $value){
            return false;
        }
    return $ok;
}
