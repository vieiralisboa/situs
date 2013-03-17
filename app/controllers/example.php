<?php

/**
 * REST Controller
 * A controller is created automaticaly if the Api called does not have one
 * and the its schema already exists in the schemas folder.
 * The controller has to be created manualy, in the controllers folder,
 * if the Api does not use the database (that is: it does not have a schema).
 *
 * Anonymous functions require PHP >=5.3. 
 *
 * @example For a  tasks controller 'tasks.php':
 * change table class name 'Example_Controller' to 'Tasks_Controller'
 * change first path name '/example' to '/tasks'
 */
class Example_Controller {
    
    /**
     * GET
     */
    public function get() {

        Router::route('/example', function() { 
            return Database::all();
        });

        Router::route('/example/:id', function($request) {
            return Database::find($request->data['id']);
        });

        Router::route('/example/done', function() {
            return Database::query("SELECT * FROM todos WHERE done = 1;");
        });

        Router::route('/example/delete/:id', function($request) {    
            return Database::delete($request->data['id']);
        });

        Router::route('/example/seed', function() {
            $todos = array(
                array('id'=>1, 'title'=>"Go to the supermarket.", 'done'=>1),//, 'time'=>'2013-01-25 12:23:55'
                array('id'=>2, 'title'=>"Practice guitar.", 'done'=>0),
                array('id'=>3, 'title' => "Wash the dishes.", 'done'=>0));
            return Database::seed($todos);
        });
    }

    /**
     * POST
     */
    public function post() {
       Router::route('/example', function($request) {    
            $todo = new Record($request->input);
            return $todo->save();#return $todo;
        });
    }

    /**
     * UPDATE
     */
    public function put(){
        Router::route('/example/:id', function($request) {    
            $todo = new Record($request->input);
            return $todo->save($request->data['id']);#return $todo;
        });
    }

    /**
     * DELETE
     */
    public function delete($request){
        Router::route('/example/:id', function($request) {    
            return Database::delete($request->data['id']);
        });
    }

    public function options(){ 
        return "";
    }
}