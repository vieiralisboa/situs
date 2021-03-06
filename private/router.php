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
 * Router class
 */
class Router extends Util {
    protected $response = "";
    protected static $routes;
    public static $controller_config;
    public static $json = true;
    protected $config;

    /**
     * Route
     * adds a route callback to the routes array
     */
    public static function route($uri, $callback){
        if(empty($uri) || empty($callback)) return;
        if(!isset(self::$routes)) self::$routes = (object) array();
        self::$routes->$uri = $callback;
    }

    public static function html($html)
    {
        self::$json = false;
        header('Content-Type: text/html; charset=utf-8');
        die($html);
    }

    public static function text($text)
    {
        self::$json = false;
        header('Content-Type: text/plain; charset=utf-8');
        die($text);
    }

    /**
     * Request
     * parses the request headers
     */
    protected function request() {
        if(isset($this->request)) return $this->request;

        $this->request = (object) array();
        $this->request->method = strtolower($_SERVER['REQUEST_METHOD']);
        $this->request->input = json_decode(file_get_contents("php://input"));
        $this->request->uri = $this->ctype_array($this->break_uri($_SERVER['REQUEST_URI']));

        return $this->request;
    }

    /**
     * creates a controller from the controller template
     * is its schema exists
     */
    public static function create_controller($api){
        $base = dirname(__FILE__);
        $controller = "$base/controllers/$api.php";

        // controller already exists
        if(file_exists($controller)) return false;

        $schema = "$base/schemas/$api.php";

        // schema exists
        if(file_exists($schema)) {
            return self::make_controller($controller, $api);
        }
        // schema not found
        else {
            if(self::create_schema($schema, $api)){
                return self::make_controller($controller, $api);
            }
        }
    }

    /**
     * Make Controller
     * Will overwrite any existing controller!
     */
    public static function make_controller($controller, $api){

        $base = dirname(__FILE__);
        $template = "$base/templates/controller.template";

        $names = array('Name'=>ucfirst($api),'name'=>$api);
        $data = file_get_contents($template);

        foreach($names as $name => $value) {
            $data = str_replace("{".$name."}", $value, $data);
        }

        file_put_contents($controller, $data);

        if(file_exists($controller)) return true;

        // failed to create the controller
        return false;
    }

    /**
     * creates a schema from the schema template
     * is its schema exists
     */
    public static function create_schema($schema, $api){
        // schema exists?
        if(file_exists($schema)) return false;

        $base = dirname(__FILE__);
        $json = "$base/schemas/$api.json";

        // json schema exists
        if(file_exists($json)) return self::make_schema($json);

        // no json schema found
        return false;
    }

    /**
     * Make Schema
     * Will overwrite any existing schema!
     */
    public static function make_schema($json){
        $file = explode(".", basename($json));
        $api = $file[0];
        $schema = json_decode(file_get_contents($json));

        #print_r($schema);

        $columns = array();
        foreach($schema->columns as $column){

            $string = "$column->name $column->type";

            if(isset($column->constraints))
                $string .= " ".implode(" ", $column->constraints);

            if(isset($column->default)){
                $default = $column->default;
                $string .= " DEFAULT $default";
            }

            $columns[] = $string;
        }

        $template = dirname(dirname($json))."/templates/schema.template";

        $names = array(
            'Table'=>ucfirst($api),
            'table'=>$api,
            'schema'=>implode(",\n", $columns),
            'Controller' => ucfirst(strtolower($schema->PDO)),
        );
        $data = file_get_contents($template);

        foreach($names as $name => $value){
            $data = str_replace("{".$name."}", $value, $data);
        }

        $filename = dirname($json)."/$api.php";

        file_put_contents($filename, $data);

        #die("DONE");

        if(file_exists($filename)) return true;
        return false;
    }

    /**
     * Start the (Router) Controller
     */
    public function start ($request) {
        // controller name
        $api = $request->uri[0];// situs.pt/$api/:some

        // requires PHP version 5.3+
        if(floatval(phpversion()) < 5.3) self::quit(501);

        define("SITUS", dirname(dirname(__FILE__)));
        define("WWW", dirname(SITUS));

        $temp_folder = WWW."/temp/";
        if (!file_exists($temp_folder)) {
            mkdir($temp_folder, 0777, true);
        }
        define("TEMP_FOLDER", WWW."/temp/");

        //------------
        // controller
        //------------
        // framework root
        $root = dirname(__FILE__);
        // controllers folder
        $folder = "$root/controllers/$api";
        // controller filename
        $controller = "$folder/$api.php";

        //---------------------
        // load the Controller
        //---------------------
        // if controller loading fails...
        if(!load($controller)) {
            //--------------------------------
            // try loading legacy controllers
            //--------------------------------
            $folder = "$root/controllers";
            $controller = "$folder/$api.php";
            if(!load($controller)) {
                // create (table) controller (if a schema exists) or quit
                if(!self::create_controller($api)) self::quit(404);
                // retry loading the controller or quit
                if(!load($controller)) self::quit(404);
            }
        }

        //--------------------
        // run the Controller
        //--------------------
        // Controller class name
        $Controller = ucwords($api) . "_Controller";
        // Controller
        if(class_exists($Controller)) {
            $args = array();
            $args[] = $request;
            if(method_exists($Controller, $request->method)) {
                //-------------------------------------
                // Basic Auth to access the controller
                //-------------------------------------
                Auth::basic($api);

                // ...
                Database::$table = $api;

                //--------
                // Config 
                //--------
                // load controller config
                $jsonfile = "$folder/$api.json";
                if(file_exists($jsonfile)){
                    $json = file_get_contents($jsonfile);
                    self::$controller_config = json_decode($json);
                    if(empty(self::$controller_config->WWW)) self::$controller_config->WWW = WWW;
                }

                // invoke the controller
                $reflectionMethod = new ReflectionMethod($Controller, $request->method);
                $this->response = $reflectionMethod->invokeArgs(new $Controller(), $args);

                //--------
                // Routes
                //--------
                // controller has routes
                if(count(self::$routes) > 0) {
                    // find a route match
                    foreach(self::$routes as $route => $callback) {
                        // route match found
                        if($request->data = $this->preg_match_uri($route)) {
                            $this->response = $callback($request);
                            break;
                        }
                    }
                    // route not found
                }
                // controler has no routes or route not found
                #if(is_string($this->response)) $this->json = false;
                return;
            }
        }

        self::quit(404);
    }

    /**
     * Run
     * Auto starts a new Router
     */
    public static function run($config = null) {
        // using a aquery
        if(!empty($_SERVER['QUERY_STRING'])) {
            $_SERVER['REQUEST_URI'] = "/".strtolower($_SERVER['QUERY_STRING']); 
        }

        /*/TODO get config from json file
        if($config === null){
            $config = (object) array();
            $config->activity = (object) array();
            $config->activity->filename = "../storage/activity.json";
        }//*/

        return new Router($config);
    }

    /**
     * Read config (json) file
     * @param string $config config file name
     */
    protected function config($config = false){
        $file = dirname(__FILE__)."/config.json";

        #$this->stop(getcwd());// "/public/"

        if(file_exists($file)) {
            return $this->config = json_decode(file_get_contents($file));
        }
        else {
            return $this->config = (object) array();
        }
    }

    /**
     * Stop
     */
    protected function stop($message = null) {
        if($message != null) $this->response = $message;
        exit;
    }

    /**
     * Redirects to other url
     */
    public static function redirect($url){
        header("Location: $url");
        exit;
    }

    function __construct($config){
        ini_set('date.timezone', 'Europe/Lisbon');
        header('X-Powered-By: PHP/'.phpversion().' Situs');

        // ignore OPTIONS method
        if($_SERVER['REQUEST_METHOD'] == "OPTIONS"){

            // send OPTIONS headers here

            exit;
        }

        //---------------------------------------------
        // Please review the config file 'config.json'
        //---------------------------------------------

        // Config $this->config();
        $this->config($config);

        // offline?
        if($this->config->offline) {
            $this->response = "offline";
            exit;
        }

        // force ssl?
        if(isset($this->config->ssl) && $this->config->ssl){
            if(!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != "on"){
                header("Location: https://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]);
                exit;
            }
        }

        // log activity?
        // TODO log activity to a database
        if(isset($this->config->activity) && is_object($this->config->activity)){
            if(isset($this->config->activity->disabled) && !$this->config->activity->disabled){
                Activity::log($this->config->activity);
            }
        }

        // use .htaccess file or uncomment the 1st line in the if block
        // or neither for a "404 Not Found" status at "/"
        if($_SERVER['REQUEST_URI'] == "/" || $_SERVER['REQUEST_URI'] == ""){
            #$this->redirect("/index.html");

            $dir = scandir(dirname(__FILE__)."/controllers");
            $controllers = (object) array();

            foreach ($dir as $key => $value) {
                if($value != "." && $value != ".."){
                    $controller = preg_replace('/\\.[^.\\s]{3,4}$/', '', $value);
                    $controllers->$controller =$_SERVER['REQUEST_SCHEME']."://". $_SERVER['HTTP_HOST']."/". $controller;
                }
            }

            $this->stop($controllers);
        } 

        //--------------
        // http request
        //--------------
        // process the request
        $request = $this->request();
        //DEVELOPMENT ONLY
        #$this->stop($request);
        // satisfy the request
        $this->start($request);
    }

    function __destruct(){
        #header('X-Powered-By: PHP/'.phpversion().' Situs');

        #if($this->response === null)
        #{
        #    $this->response = '';
        #}

        #$this->json = $this->response !== '' ? $this->json : false;

        if( self::$json ){
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Content-type: application/json; charset=utf-8');

            echo json_encode($this->response);
        }
        else echo $this->response;

        self::$json = true;
    }
}
