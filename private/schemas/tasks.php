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
 * Example tasks table schema
 * @example For a tasks schema 'tasks.php':
 * change table class name 'Example_Table' to 'Tasks_Table'
 * change table name 'example' to 'tasks'
 * change controller name to the PDO used (Sqlite, Mysql, ...)
 */
class Tasks_Table extends Sqlite {

    /**
     * creates the table in the database
     */
    public function create(){
        $this->exec("CREATE TABLE IF NOT EXISTS tasks(id INTEGER PRIMARY KEY,
date_created TEXT DEFAULT CURRENT_TIMESTAMP,
last_updated TEXT DEFAULT CURRENT_TIMESTAMP,
title TEXT NOT NULL,
done INTEGER DEFAULT 0,
priority INTEGER DEFAULT 5)");
    }

    /**
     * removes the table from the database
     */
    public function drop(){
        $this->exec("DROP TABLE tasks");
    }
}
