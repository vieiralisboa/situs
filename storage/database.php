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
class Database extends Sqlite {
    
    public static $table = null;

    /**
     * Static Database...
     */
    public static function db ()
    {
         return new Database();
    }


    /**
     * executes a SQL query
     */
    public static function query($sql)
    {
        // validate sql here

        $result = self::db()->connect()->query($sql);

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
    public static function all()
    {

        $db = self::db();

        $result = $db->connect()->query("SELECT * FROM ".self::$table);
        
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
    public static function find($id)
    {
        $table = self::$table;
        $sql = "SELECT * FROM $table WHERE id = ". $id;
        $result = self::db()->connect()->query($sql);
        $rows = array();
        if(!empty($result)){
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $rows[] = Util::ctype_array($row);
            }        
        }
        return $rows[0];
    }


    /**
     * Inserts a record into the database
     */
    private function insert()
    {
        $data = $this->validate_schema_fields();

if(!count($data['fields']) || !count($data['values']))
    Util::quit(417, "FAILED TO GET SCHEMA FIELDS");//json_encode($data));        

        $sql = "INSERT INTO {table}({fields}) VALUES({values});";
        $sql = str_replace("{table}", self::$table, $sql);
        $sql = str_replace("{fields}", implode(", ", $data['fields']), $sql);
        $sql = str_replace("{values}", implode(", ", $data['values']), $sql);

        $db = $this->connect();
   
        try {
            $result = $db->exec($sql);
        }
        catch(PDOException $Exception) {
            Util::quit(417, $Exception->getMessage()." [".$Exception->getCode()."]");
        }
        
        if($result) {
            $id = (int) $db->lastInsertId();
            if(!$id) Util::quit(417, "id = $id");      
            return $id;
        }
        
        Util::quit(417, "result: '$result'"); 

        return null;
    }


    /**
     * Updates a record in the database
     */
    private function update($id)
    {
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
    }


     /**
     * Deletes a record from the database
     */
    public static function delete($id)
    {
        $table = self::$table;
        $sql = "DELETE FROM $table WHERE id = $id;";
        return self::db()->connect()->exec($sql);
    }


    /**
     * Saves a record to the database
     */
    public function save ($id = null)
    {
        #$numargs = func_num_args();
        #$args = func_get_args();
        
        $table = self::$table;    

        if($id === null) {
            $id = $this->insert();
        } 
        else {
            $this->update($id);
        } 
        
        if($id === null) Util::quit(417, "INSERT INTO $table FAILED");

        $result = Database::find($id);

        // parse result here

        return $result;
    }
    

    /**
     * use table schema to create table in the database
     */
    public static function create_table($table)
    {
        $sqlite = new Sqlite;

        /* using schema.sql
        if(! $sqlite->table_exists($table)) {
            $base = dirname(dirname(__FILE__));
            $filename = "$base/app/schemas/$table.sql";
            if(file_exists($filename)) 
                $sqlite->exec("CREATE TABLE IF NOT EXISTS $table(".file_get_contents($filename).")");
        }
        */

        //* using schema.php
        // if the table desn't exist
        if(!$sqlite->table_exists($table)) {
#Util::quit(417, "'$table' NO EXISTS");

            $root = dirname(dirname(__FILE__));
            $schema_file = "$root/private/schemas/$table.php";
#Util::quit(417, "'$schema_file'");

            if(!load($schema_file)) {
#Util::quit(417, "'$schema_file' DOES NO EXIST");
                return -1;
            }

            $Schema = ucwords($table)."_Table";
            if (class_exists($Schema)){
                $schema = new $Schema();
                $action = 'create';
                if(method_exists($schema, $action)){
                    $schema->{$action}();
                }
            }

            //TODO verify that table was created
            return 1;// created table
        }
        else  {
#Util::quit(417, "'$table' ALREADY EXISTS");
            return 0;// did not create table (table already exists)
        }
        //*/
    }
    
    /**
     * Delete table
     */
    public static function drop_table($table)
    {
        $sqlite = new Sqlite;
        if($sqlite->table_exists($table)) return $sqlite->exec("DROP TABLE todos");
        return false;
    }

    /**
     * Populates table
     */
    public static function seed($records)
    {
        $table = self::$table;
        self::create_table($table);

        $sqlite = new Sqlite;
        $schema = $sqlite->schema($table);
 
        $fields = $types= array();
        foreach($schema as $field => $type) {
            $fields[] = $field;
            $types[$field] = $schema[$field][0];
        }

        // Prepare INSERT statement to SQLite3 file db
        $sql = "INSERT INTO $table({fields}) VALUES (:{values});";
        $sql = str_replace("{fields}", implode(", ", $fields), $sql);
        $sql = str_replace("{values}", implode(", :", $fields), $sql);

        $stmt = self::db()->connect()->prepare($sql);
        
        // Bind parameters to statement variables
        foreach($fields as $field) {
            $stmt->bindParam(":$field", $$field);
        }

        // Loop thru all messages and execute prepared insert statement
        foreach($records as $record) {
            foreach($fields as $field) $$field = $record[$field];
            // Execute statement
            $stmt->execute();
        }
        return $stmt->rowCount();
    }


    /**
     * Validates input data for/to its table (fields) schema
     */
    private function validate_schema_fields()
    {
        $input = $this->fields;
        $schema = $this->schema();
        $fields = $values = array();     

#Util::quit(417, "validate_schema_fields: ".json_encode(array($input, $schema)));  

        foreach($input as $field => $value) {
            $fields[] = $field;
            $values[] = $schema[$field][0] == "TEXT" ? "'$value'" : $value;
        };
        return array('fields'=>$fields, 'values'=>$values);
    }

   
    function __construct($input = null)
    {
        if($input === null) return;

        if(self::create_table(self::$table) == -1) 
            Util::quit(417, "FAILED TO CREATE '".self::$table."' TABLE");
        
        $schema = $this->schema(self::$table);
    
        $this->input = $input;
        
if(!count($schema)) Util::quit(417, "FAILED TO GET '".self::$table."' SCHEMA");

        $this->fields = (object) array();
        foreach($input as $field => $value){
            if(isset($schema[$field])){
                $this->fields->$field = $value;
            }
        };
    }
}
