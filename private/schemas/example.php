<?php

/**
 * Example tasks table schema
 * @example For a tasks schema 'tasks.php':
 * change table class name 'Example_Table' to 'Tasks_Table'
 * change table name 'example' to 'tasks'
 */
class Example_Table extends Sqlite {

    /**
     * creates the table in the database
     */
    public function create(){
        $this->exec("CREATE TABLE IF NOT EXISTS example(
        id INTEGER PRIMARY KEY,
        title TEXT,
        done INTEGER default 0,
        date_created TEXT default CURRENT_TIMESTAMP,
        last_updated TEXT default CURRENT_TIMESTAMP)");
    }

    /**
     * removes the table from the database
     */
    public function drop(){
        $this->exec("DROP TABLE example");
    }
}
