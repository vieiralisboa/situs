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
 * TODO change Model to Record (or Document)
 */
class Record {

    /**
     *
     */
    public function save ($id = null){
        
        $record = new Database($this->attributes());
        
        $result = $record->save($id);
        
        // update model here with the results
        
        #$this->attributes($result);// does not set null attributes
        foreach($result as $attr => $val) $this->$attr = $val;// sets null attributes

        return $this;//chainning
    }
    
    /**
     *
     */
    public function attribute($attribute, $value) {
        if(isset($value)) $this->$attribute = $value;
        if(isset($this->$attribute)) return $this->$attribute;
        return false;
    }

    /**
     *
     */
    public function attributes($attributes) {    
        if(isset($attributes)) {
            #if(is_object($attributes)){
                foreach($attributes as $name => $attribute) {
                    $this->attribute($name, $attribute);
                }
            #}
            #else return false;
        }
        return get_object_vars($this);
    }

    function __construct($attributes){ 
        $this->attributes($attributes); 
    }
}