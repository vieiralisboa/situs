<?php
/**
 * Situs - A PHP Framework
 *
 * @package  Situs
 * @version  0.0.0
 * @author   Jose Vieira Lisboa <jose.vieira.lisboa@gmail.com>
 * @link     http://situs.pt
 */

/**
 * RESTful Controller
 */
class Tasks_Controller {
    
    /**
     * GET
     */
    public function get(){     
        
        Router::route('/tasks', function(){ 
            // gets all records from the database
            return Database::all();
        });

        Router::route('/tasks/:id', function($request){
            // gets a record by its id from the database
            return Database::find($request->data['id']);
        });    

        Router::route('/tasks/seed', function($records) {
            /* Example $records
            $records = array(
                array('id'=>1, 'title'=>"Go to the supermarket.", 'done'=>1),
                array('id'=>2, 'title'=>"Practice guitar.", 'done'=>0),
                array('id'=>3, 'title' => "Wash the dishes.", 'done'=>0));
            */
            // inserts an array of records in the database
            return Database::seed($records);
        });

        Router::route('/tasks/json/:json', function($request) {    
            $request->input = (array) json_decode(urldecode($request->data['json']));
            $todo = new Record($request->input);
            return $todo->save();
        });

        Router::route('/tasks/delete/:id', function($request) {    
            // delets a record by its id in the database
            return Database::delete($request->data['id']);
        });

        Router::route('/tasks/update/:json', function($request) {    
            $request->input = (array) json_decode(urldecode($request->data['json']));
            $request->data['id'] = $request->input['id'];
            // updates a record by its id in the database
            $record = new Record($request->input);
            return $record->save($request->data['id']);
        });
    }

    /**
     * POST
     */
    public function post() {
       
       Router::route('/tasks', function($request) {    
            // inserts a record in the database 
       		$record = new Record($request->input);
            return $record->save();
        });
    }

    /**
     * UPDATE
     */
    public function put(){
        
        Router::route('/tasks/:id', function($request) {    
        	// updates a record by its id in the database
        	$record = new Record($request->input);
            return $record->save($request->data['id']);
        });
    }

    /**
     * DELETE
     */
    public function delete($request){
        
        Router::route('/tasks/:id', function($request) {    
            // delets a record by its id in the database
            return Database::delete($request->data['id']);
        });
    }
}