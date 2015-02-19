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
class Study_Controller {
    
    /**
     * GET
     */
    public function get() {
        
        #header('Content-Type: text/html; charset=utf-8');
        
        Router::route('/study', function() { 
            return "Study what?";
        });

        Router::route('/study/:what', function($request) {  
            $frontgate = file_get_contents("http://situs.no-ip.org:8080/frontgate/frontgate-min.js");
            
            // can't change headers (already sent?)
            #header('Content-Type: application/javascript');

            header("Location: http://localhost:8080/study/{$request->data['what']}/index.html");
            exit;
        });
    }
}