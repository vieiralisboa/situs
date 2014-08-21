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
 * SQL controller for Sqlite PDO
 */
class Sqlite {
    /**
     * establishes a database connection
     */
    protected function connect() {
        if(isset($this->db)) return $this->db;
        $base = dirname(__FILE__);
        if(file_exists("$base/sqlite.json")){
            $config = json_decode(file_get_contents("$base/sqlite.json"));
            if(!empty($config->base)) $base = $config->base;
        }

        $database = "sqlite:$base/situs.sqlite";
        $this->db = new PDO($database);
        return $this->db;
    }

    /**
     * executes SQL queries
     */
    protected function exec($sql) {

        //parse sql here

        return $this->connect()->exec($sql);
    }

    /**
     * Checks if a table exists in database
     */
    public function table_exists($table) {
        $sql = "SELECT name FROM sqlite_master WHERE type = 'table' AND name = '$table'";
        $query = $this->connect()->query($sql);
        return $query->fetch() === false ? false : true;
    }

    /**
     * Parses the Create Table Statement
     */
    public function schema($table) {
        if(isset($this->schema)) return $this->schema;

        $sql = "SELECT sql FROM sqlite_master WHERE type='table' AND name = '$table';";
        $query = $this->connect()->query($sql);
        $create = $query->fetch();
        $create = explode("\n", $create[0]);
        $this->schema = array();
        for($i=1; $i < count($create); $i++){
            $trimmed = str_replace(array(",",")"), "", trim($create[$i]));
            $parts = explode(" ", $trimmed);
            $column = array_shift($parts);
            $this->schema[$column] = $parts;
        }

        return $this->schema;
    }
}
