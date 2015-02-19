<?php
/**
 * Situs - A PHP Framework
 *
 * @package  Situs
 * @version  0.0.0
 * @author   José Vieira Lisboa <jose.vieira.lisboa@gmail.com>
 * @link     http://situs.pt
 */

/**
 * Link to the database
 */
class Recursos extends Sqlite {

    /**
     * Static Database...
     */
    public static function db () {
        return new Recursos();
    }

    /**
     * executes a SQL query
     */
    public static function query($sql) {

        //validate sql here

        $result = self::db()->connect(dirname(__FILE__)."/recursos.sqlite")->query($sql);

        $rows = array();
        if(!empty($result)) {
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $rows[] = Util::ctype_array($row);
            }
        }
        return $rows;
    }

    /**
     * Fetches all records from the database
     */
    public static function all($table) {
        $db = dirname(__FILE__)."/recursos.sqlite";
        $result = self::db()->connect($db)->query("SELECT * FROM $table");

        $rows = array();
        if(!empty($result)) {
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $rows[] = Util::ctype_array($row);
            }
        }
        return $rows;
    }

    /**
     * Fetches one record from the database
     */
    public static function find($id, $table){
        $db = dirname(__FILE__)."/recursos.sqlite";



        $sql = "SELECT * FROM $table WHERE R_ID = $id";
        $result = self::db()->connect($db)->query($sql);


        $rows = array();
        if(!empty($result)){
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $rows[] = Util::ctype_array($row);
            }
        }

        if(!count($rows)) return null;
        return $rows[0];
    }

    /* Inserts a record into the database*/
    private function insert(){
/*
        $data = $this->validate_schema_fields();
        $sql = "INSERT INTO {table}({fields}) VALUES({values});";
        $sql = str_replace("{table}", self::$table, $sql);
        $sql = str_replace("{fields}", implode(", ", $data['fields']), $sql);
        $sql = str_replace("{values}", implode(", ", $data['values']), $sql);

        $database = $this->connect();

        if($database->exec($sql)){
            $id = (int) $database->lastInsertId();
            return $id;
        }

        return null;
*/
    }

    /*/Updates a record in the database
    private function update($id){
        $this->fields->last_updated = date("Y-m-d H:m:s");

        $data = $this->validate_schema_fields();
        $pairs = array();
        for($i=0; $i < count($data['fields']); $i++){
            $pairs[] = $data['fields'][$i] . " = " . $data['values'][$i];
        }

        $sql = "UPDATE {table} SET {pairs} WHERE id = $id;";
        $sql = str_replace("{table}", self::$table, $sql);
        $sql = str_replace("{pairs}", implode(", ", $pairs), $sql);

        $database = $this->connect();
        if($database->exec($sql)) return $id;
        return null;
    } */

    /**
     * Deletes a record from the database
     */
    public static function delete($table, $rule){
        $sql = "DELETE FROM $table WHERE {$rule[0]} = {$rule[1]};";

        return self::db()->connect(dirname(__FILE__)."/recursos.sqlite")->exec($sql);
    }

    /**
     * Saves a record to the database
     */
    public function save ($id = null, $table){

        if($id === null) $id = $this->insert();
        else $this->update($id);

        return Database::find($id);
    }


    /**
     * use table schema to create table in the database
     */
    public static function create_table($table){
        $sqlite = new Sqlite;

        // using schema.php
        // if the table desn't exist
        if(! $sqlite->table_exists($table)) {
            $base = dirname(dirname(__FILE__));
            $schema_file = "$base/app/schemas/$table.php";

            load($schema_file);

            $Schema = ucwords($table)."_Table";
            if (class_exists($Schema)){
                $schema = new $Schema();
                $action = 'create';
                if(method_exists($schema, $action)){
                    $schema->{$action}();
                }
            }
        }
    }

    /**
     * Delete table
     */
    public static function drop_table($table){
        $sqlite = new Sqlite;
        if($sqlite->table_exists($table)) return $sqlite->exec("DROP TABLE $table");
        return false;
    }

    function __construct($input = null){
        if($input === null) return;

        $this->input = $input;
    }
}
