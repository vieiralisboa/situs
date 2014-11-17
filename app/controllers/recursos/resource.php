<?php

class Resource
{
    protected $db;
    public static $rendimento_query;
    public static $composto_query;

    public static function db()
    {
        return new Resource();
    }

    public function query($query)
    {
       $stmt = $this->db->query($query);
       return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function table($table)
    {
       $stmt = $this->db->query("SELECT * FROM $table");
       return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function recurso($id)
    {
        try {
            $stmt = $this->db->query("SELECT * FROM RESOURCE WHERE id = $id");
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
            if($recurso['type_code'] == "RCO") $price += $this->composto($recurso['id']);
            else $price += $recurso['price']*$recurso['quantity'];
        }

        return round($price,2);
    }//*/

    // obtém rendimento recursivo
    public function recursivo($rendimento)
    {
        foreach ($rendimento['recursos'] as $key => $recurso) {
            if($recurso['type_code'] == "RCO") {
                $rendimento['recursos'][$key] = $this->recursivo($this->rendimento($recurso['id']));
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
        $result['recurso']['id'] = intval($result['recurso']['id']);

        // composto
        if($recurso[0]['type_code'] == "RCO") {
            try {
                $recursos = $this->query(str_replace("{id}", $recurso[0]['id'], self::$rendimento_query));
            }
            catch(PDOException $ex) {
                return $ex->getMessage();
            }

            foreach ($recursos as $key => $value) {
                $id = intval($value['id']);

                if($value['type_code'] == "RCO") $price = $this->composto($id);
                else $price = floatval($value['price']);

                $quantity = floatval($value['quantity']);
                $parcial = round($price*$quantity, 2);
                $total += $parcial;

                $recursos[$key]['id'] = $id;
                $recursos[$key]['price'] = $price;
                $recursos[$key]['quantity'] = $quantity;
                $recursos[$key]['parcial'] = $parcial;
            }

            $result['recurso']['price'] = $total;
            $result['recursos'] = $recursos;

            return $result;
        }

        $result['recurso']['supplier_id'] = intval($result['recurso']['supplier_id']);
        $result['recurso']['price'] = floatval($result['recurso']['price']);
        return $result;
    }

    function __construct(){
        $this->db = new PDO('mysql:host=situs.pt;dbname=FICHAS;charset=utf8', Auth::user(), Auth::pw(),
            array(PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
    }
}

Resource::$rendimento_query = <<<SQL1
SELECT RESOURCE.id, RESOURCE.name, AMOUNT.quantity, RESOURCE.price, RESOURCE.type_code
FROM AMOUNT, RESOURCE
WHERE AMOUNT.composite_id = {id}
AND RESOURCE.id = AMOUNT.resource_id;
SQL1;

Resource::$composto_query = <<<SQL2
SELECT RESOURCE.id, AMOUNT.quantity, RESOURCE.price, RESOURCE.type_code
FROM AMOUNT, RESOURCE
WHERE AMOUNT.composite_id = {id}
AND RESOURCE.id = AMOUNT.resource_id
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
