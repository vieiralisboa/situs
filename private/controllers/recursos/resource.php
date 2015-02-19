<?php

class Resource
{
    public $db;
    public static $rendimento_query;
    public static $composto_query;

    //
    public static function db($config)
    {
        return new Resource($config);
    }

    //
    public function query($query)
    {
        try {
            $stmt = $this->db->query($query);
        }
        catch(PDOException $ex) {
            return $ex->getMessage();
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //
    public function table($table)
    {
       $stmt = $this->db->query("SELECT * FROM $table");
       return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //
    public function tables($db)
    {
       $stmt = $this->db->query("SHOW TABLES FROM $db");
       return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //
    public function recurso($id)
    {
        try {
            $stmt = $this->db->query("SELECT * FROM RECURSO WHERE RECURSO_ID = $id");
        }
        catch(PDOException $ex) {
            return $ex->getMessage();
        }

       return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //*/ obtém (recursivamente) o preço de recurso composto
    public function composto($id)
    {
        $price = 0;
        $recursos = $this->query(str_replace("{id}", $id, self::$composto_query));

        foreach ($recursos as $key => $recurso) {
            if($recurso['TIPO_CODIGO'] == "COMP") $price += $this->composto($recurso['RECURSO_ID']);
            else $price += $recurso['RECURSO_PRECO'] * $recurso['QUANTIDADE'];
        }

        return round($price,2);
    }//*/

    // obtém rendimento recursivo
    public function recursivo($rendimento)
    {
        foreach ($rendimento['recursos'] as $key => $recurso) {
            if($recurso['TIPO_CODIGO'] == "COM") {
                $rendimento['recursos'][$key] = $this->recursivo($this->rendimento($recurso['RECURSO_ID']));
            }
        }

        return $rendimento;
    }

    // obtém rendimento imediato
    public function rendimento($id)
    {
        try {
           $recurso = $this->recurso($id);
        }
        catch(PDOException $ex) {
            return $ex->getMessage();
        }

        $result = array();
        $total = 0;
        $result['recurso'] = $recurso[0];
        //$result['rendimento'] = null;
        //$result['recurso']['RECURSO_ID'] = intval($result['recurso']['RECURSO_ID']);

        // composto
        if($recurso[0]['TIPO_CODIGO'] == "COM") {
            try {
                $recursos = $this->query(str_replace("{id}", $recurso[0]['RECURSO_ID'], self::$rendimento_query));
            }
            catch(PDOException $ex) {
                return $ex->getMessage();
            }

            foreach ($recursos as $key => $value) {
                $id = intval($value['RECURSO_ID']);

                if($value['TIPO_CODIGO'] == "COMP") $price = $this->composto($id);
                else $price = floatval($value['RECURSO_PRECO']);

                $quantity = floatval($value['QUANTIDADE']);
                $parcial = round($price*$quantity, 2);
                $total += $parcial;
                

                $recursos[$key]['RECURSO_ID'] = $id;
                $recursos[$key]['FORNECEDOR_ID'] = intval($value['FORNECEDOR_ID']);
                $recursos[$key]['RECURSO_PRECO'] = $price;
                $recursos[$key]['QUANTIDADE'] = $quantity;
                $recursos[$key]['PRECO_PARCIAL'] = $parcial;
            }

            $result['recurso']['RECURSO_PRECO'] = $total;
            $result['recursos'] = $recursos;
            //$result['rendimento'] = array();

            return $result;
        }

        //$result['recurso']['supplier_id'] = intval($result['recurso']['supplier_id']);
        $result['recurso']['RECURSO_PRECO'] = floatval($result['recurso']['RECURSO_PRECO']);
        return $result;
    }

    function __construct($config)
    {
        $user = isset($config->user) ? $config->user : Auth::user();
        $pw = isset($config->pw) ? $config->pw : Auth::pw();

        $pdo = "mysql:host={host};dbname={dbname};charset={charset}";

        if(empty($config->host) || empty($config->dbname)) Util::quit(500);
        $pdo = str_replace("{host}", $config->host, $pdo);
        $pdo = str_replace("{dbname}", $config->dbname, $pdo);

        if(empty($config->charset)) $config->charset = "utf8";
        $pdo = str_replace("{charset}", $config->charset, $pdo);

        $this->db = new PDO($pdo, $user, $pw,
            array(PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
    }
}

//
Resource::$rendimento_query = <<<SQL1
SELECT RECURSO.FORNECEDOR_ID, RECURSO.RECURSO_ID, RECURSO.NOME, RENDIMENTO.QUANTIDADE, RECURSO.UNIDADE_CODIGO, RECURSO.RECURSO_PRECO, RECURSO.TIPO_CODIGO
FROM RENDIMENTO, RECURSO
WHERE RENDIMENTO.REC_RECURSO_ID = {id}
AND RECURSO.RECURSO_ID = RENDIMENTO.RECURSO_ID;
SQL1;

//
Resource::$composto_query = <<<SQL2
SELECT RECURSO.RECURSO_ID, RENDIMENTO.QUANTIDADE, RECURSO.UNIDADE_CODIGO, RECURSO.RECURSO_PRECO, RECURSO.TIPO_CODIGO
FROM RENDIMENTO, RECURSO
WHERE RENDIMENTO.REC_RECURSO_ID = {id}
AND RECURSO.RECURSO_ID = RENDIMENTO.RECURSO_ID;
SQL2;

// ----- CONNECT -----
/*/ OLD WAY
$link = mysql_connect('localhost', 'user', 'pass');
mysql_select_db('testdb', $link);
mysql_set_charset('UTF-8', $link);
//*/
// PDO WAY
#$db = new PDO('mysql:host=situs.pt;dbname=FICHAS;charset=utf8', 'user', 'pass');

/*/ ----- SET OPTIONS -----
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
// OR ...
#$db = new PDO('mysql:host=situs.pt;dbname=FICHAS;charset=utf8', 'user', 'pass',
#    array(PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
//*/

// ----- ERROR HANDLING -----
/*/ OLD WAY
//connected to mysql
#$result = mysql_query("SELECT * FROM table", $link) or die(mysql_error($link));
//*/
/*/ PDO WAY
try {
    //connect as appropriate as above
    $db->query('hi'); //invalid query!
}
catch(PDOException $ex) {
    #return "An Error occured!"; //user friendly message
    #some_logging_function(return $ex->getMessage());
    return $ex->getMessage();
    
}
//*/
