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
 *
 */
class Util {

    public static $codes = array (
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        426 => 'Upgrade Required',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        509 => 'Bandwidth Limit Exceeded',
        510 => 'Not Extended'
    );

    /**
     * ctype Array Values
     * Converts ctype strings in array to numeric values
     */  
    public static function ctype_array($array){   
        foreach($array as $key => $value){
            $array[$key] = ctype_digit($value) ? (int) $value : $value;
        }
        return $array;
    }

    /**
     * Break uri Apart
     */
    public static function break_uri($uri){
        $string = str_replace('\/', '/', $uri);
        $string = str_replace('//', '/',  $string);
        $array = explode('/', substr($string, 1));
        
        return $array;
    }

    /**
     * Regex Uri
     */
    public static function regex_uri($uri) {
        $uri_array = self::break_uri($uri);
        
        $regex = "/^";
        foreach($uri_array as $u) {
            $regex .= "\\/";
            $regex .= substr($u, 0, 1) == ":" ? '(?P<' . substr($u, 1) .'>[A-Za-z0-9_\-\.]+)' : $u;//
        }
        $regex .= "$/";

        return $regex;
    }

    /**
     * Preg Match Requested Uri
     * @example 
     * $_SERVER[REQUEST_URI];// "/todos/5"
     * match_uri('/todos/:id'); // array(0 => "todos", 'id' => 5)
     * match_uri('/tasks'); // false
     * @return Array matches false for no match
     */
    public function preg_match_uri($match) {
        $regex = self::regex_uri($match);
        
        if(preg_match($regex, $_SERVER['REQUEST_URI'])){
            preg_match($regex, $_SERVER['REQUEST_URI'], $matches);

            return self::ctype_array($matches);
        }        
        else return false;
    }

    public function uri($uri){
        return self::preg_match_uri($uri);
    }

    public function dummie(){
        return 'dummie';
    }

    /**
     * Quit
     */
    public static function quit($code){
        // prevent the null trail (json empty string)
        Router::$json = false;

        $status = self::$codes[$code];
        
        header($_SERVER["SERVER_PROTOCOL"]." $code $status");
       
        $response = file_get_contents(dirname(__FILE__)."/status.html");
        
        foreach(array('code'=>$code, 'status'=>$status, 'server' => $_SERVER['HTTP_HOST']) as $name => $value)
            $response = str_replace( "<%= $name %>", $value, $response);   
        
        die($response);
    }


    /**
     * Download
     * Reads (downloads) local files
     */
    public static function download($file){
        // prevent the null trail (json empty string)
        Router::$json = false;

        //!preg_match('/\.js$/', $file)
        if(!file_exists($file) || is_dir($file)) {
            self::quit(404);
        }

        $filename = basename($file);

        // Set headers
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=$filename");
        header("Content-Type: application/x-javascript");
        header("Content-Transfer-Encoding: binary");
        
         //Read the file from disk
        readfile($file);
    }

    /**
     * Upload
     * Saves uploaded files
     */
    public static function upload($file) {
        $sucess = false;
        
        // Ajax file post
        if(isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
            file_put_contents($file, $GLOBALS['HTTP_RAW_POST_DATA']);
            if(file_exists($file)) $sucess = true;
            return $sucess;        
        }

        // input post submit
        if(isset($_FILES["file"])){
            move_uploaded_file($_FILES["file"]["tmp_name"], $file);
            if(file_exists($file)) $sucess = true;
            return $sucess;
        }

        // Ajax dataSet post
        if(isset($_FILES["files"])) {
            $folder = $file;
            foreach ($_FILES["files"]["error"] as $key => $error){
                $file = $folder . $_FILES['files']['name'][$key];
                if($error == 0) //if ($error == UPLOAD_ERR_OK)//
                    move_uploaded_file($_FILES["files"]["tmp_name"][$key], $file);
                
                // TODO implement a real verify
                if(file_exists($file)) $sucess = true;
            }
            return $sucess;
        }

        return $sucess;
    }

    public function activity($config){
        if(!isset($config->filename)) return 0;

        $filename = $config->filename;

        if(file_exists($filename)) {
            $activity = json_decode(file_get_contents($filename));
        }
        else {
            $activity = (object) array();
        }

        $agent = $_SERVER['HTTP_USER_AGENT'];
        $address = $_SERVER['REMOTE_ADDR'];
        $request = $_SERVER['REQUEST_URI'];
        $today = date("Ymd");

        if(!isset($activity->$address)){
            $activity->$address = (object) array();
        }

        if(!isset($activity->$address->$agent)){
            $activity->$address->$agent = (object) array();
        }

        if(!isset($activity->$address->$agent->$today)){
            $activity->$address->$agent->$today = (object) array();
        }

        if(!isset($activity->$address->$agent->$today->$request)){
            $activity->$address->$agent->$today->$request = 0;
        }

        $activity->$address->$agent->$today->$request += 1;

        file_put_contents($filename, json_encode($activity));
    }
}
