<?php

class Contacts_Table extends Sqlite {

    /**
     * creates the table in the database
     */
    public function create(){
        $this->exec("CREATE TABLE IF NOT EXISTS contacts(
        id INTEGER PRIMARY KEY,
        first_name TEXT NOT NULL,
        last_name TEXT NOT NULL,
        email_address TEXT UNIQUE NOT NULL,
        description TEXT,
        date_created TEXT default CURRENT_TIMESTAMP,
        last_updated TEXT default CURRENT_TIMESTAMP)");
    }

    /**
     * removes the table from the database
     */
    public function drop(){
        $this->exec("DROP TABLE contacts");
    }
}
