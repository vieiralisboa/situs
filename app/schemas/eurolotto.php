<?php

class Eurolotto_Table extends Sqlite {

    /**
     * Creates the database table
     */
    public function create(){
        $this->exec("CREATE TABLE IF NOT EXISTS eurolotto (
        id INTEGER PRIMARY KEY,
        Draw INTEGER, Year INTEGER,
        Num_1 INTEGER, Num_2 INTEGER, Num_3 INTEGER, Num_4 INTEGER, Num_5 INTEGER,
        Star_1 INTEGER, Star_2 INTEGER);");
    }

    /**
     * Removes the database table
     */
    public function drop(){
        $this->exec("DROP TABLE eurolotto");
    }
}
