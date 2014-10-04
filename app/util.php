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

    public static $codes = [
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
    ];

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
     * Serve static
     * Reads local files
     */
    public static function serve($file){
        // prevent the null trail (json empty string)
        Router::$json = false;

        if(!file_exists($file) || is_dir($file)) {
            self::quit(404);
        }

        $info = pathinfo($file);

        switch(strtolower($info['extension'])){
            case "html":
            case "htm":
                header('Content-Type: text/html; charset=utf-8');
                break;

            case "css":
                header("Content-type: text/css; charset: UTF-8");
                break;

            case "json":
                header('Content-Type: application/json; charset=utf-8');
                break;

            case "js":
                header("Content-Type: application/x-javascript; charset=utf-8");
                break;

            case "vtt":
                header('Content-Type: text/vtt; charset=utf-8');
                break;

            case "txt":
            case "srt":
                header('Content-Type: text/plain; charset=utf-8');
                break;

            case "jpg":
            case "jpeg":
                header('Content-Type: image/jpeg');
                break;

            case "png":
                header('Content-Type: image/png');
                break;

            case "mp3";
                header('Content-Type: audio/mpeg');
                break;

            case "mp4";
                //header('Content-Type: video/mpeg');
                header('Content-Type: video/mp4');
                break;

            case "pdf":
                header('Content-Type: application/pdf');
                break;

            default:
                self::download($file);
                self::quit(404);
        }

        ob_clean();
        flush();

        //Read the file from disk
        readfile($file);
    }

    /**
     * Download
     * Uploads local files
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

        switch(pathinfo($filename, PATHINFO_EXTENSION)){
            case "html":
            case "htm":
                header('Content-Type: text/html; charset=utf-8');
                break;

            case "css":
                header("Content-type: text/css; charset: UTF-8");
                break;

            case "json":
                header('Content-Type: application/json');
                break;

            case "js":
                header("Content-Type: application/x-javascript");
                break;

            case "vtt":
                header('Content-Type: text/vtt; charset=utf-8');
                break;

            case "jpg":
            case "jpeg":
                header('Content-Type: image/jpeg');
                break;

            default:
                header('Content-Type: application/octet-stream');
                header("Content-Transfer-Encoding: binary");
                header('Content-Length: ' . filesize($file));
        }

/*/
$file = 'monkey.gif';
if (file_exists($file)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='.basename($file));
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    ob_clean();
    flush();
    readfile($file);
    exit;
}
//*/
        ob_clean();
        flush();

        //Read the file from disk
        readfile($file);

        exit();
    }

    /**
     * Download file range
     */
    public static function rangeDownload($file) {
        // prevent the null trail (json empty string)
        Router::$json = false;


        $fp = @fopen($file, 'rb');

        $size   = filesize($file); // File size
        $length = $size;           // Content length
        $start  = 0;               // Start byte
        $end    = $size - 1;       // End byte

        // Now that we've gotten so far without errors we send the accept range header

        // set mp4 mime-type header
        header("Content-Type: video/mp4");

        /* At the moment we only support single ranges.
         * Multiple ranges requires some more work to ensure it works correctly
         * and comply with the spesifications: http://www.w3.org/Protocols/rfc2616/rfc2616-sec19.html#sec19.2
         *
         * Multirange support annouces itself with:
         * header('Accept-Ranges: bytes');
         *
         * Multirange content must be sent with multipart/byteranges mediatype,
         * (mediatype = mimetype)
         * as well as a boundry header to indicate the various chunks of data.
         */
        header("Accept-Ranges: 0-$length");
        // header('Accept-Ranges: bytes');
        // multipart/byteranges
        // http://www.w3.org/Protocols/rfc2616/rfc2616-sec19.html#sec19.2
        if (isset($_SERVER['HTTP_RANGE'])) {
            $c_start = $start;
            $c_end   = $end;

            // Extract the range string
            list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);

            // Make sure the client hasn't sent us a multibyte range
            if (strpos($range, ',') !== false) {
                // (?) Shoud this be issued here, or should the first
                // range be used? Or should the header be ignored and
                // we output the whole content?
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header("Content-Range: bytes $start-$end/$size");
                // (?) Echo some info to the client?

                // DEBUG
                //file_put_contents("myTV_416_headers.json", json_encode(getallheaders()));

                exit;
            }

            // If the range starts with an '-' we start from the beginning
            // If not, we forward the file pointer
            // And make sure to get the end byte if spesified
            if ($range[0] == '-') {

                // The n-number of the last bytes is requested
                $c_start = $size - substr($range, 1);
            }
            else {
                $range  = explode('-', $range);
                $c_start = $range[0];
                $c_end   = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $size;
            }

            /* Check the range and make sure it's treated according to the specs.
             * http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
             */

            // End bytes can not be larger than $end.
            $c_end = ($c_end > $end) ? $end : $c_end;

            // Validate the requested range and return an error if it's not correct.
            if ($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size) {
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header("Content-Range: bytes $start-$end/$size");
                // (?) Echo some info to the client?
                exit;
            }
            $start  = $c_start;
            $end    = $c_end;
            $length = $end - $start + 1; // Calculate new content length
            fseek($fp, $start);
            header('HTTP/1.1 206 Partial Content');
        }

        // Notify the client the byte range we'll be outputting
        header("Content-Range: bytes $start-$end/$size");
        header("Content-Length: $length");

        // Start buffered download
        $buffer = 1024 * 8;
        while(!feof($fp) && ($p = ftell($fp)) <= $end) {
            if ($p + $buffer > $end) {
                // In case we're only outputtin a chunk, make sure we don't read past the length
                $buffer = $end - $p + 1;
            }
            set_time_limit(0); // Reset time limit for big files
            echo fread($fp, $buffer);
            flush(); // Free up memory. Otherwise large files will trigger PHP's memory limit.
        }

        fclose($fp);
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

    /**
     * ZeHash
     * string hash
     */
    public static function zehash($string, $options=[]){
        $algorithm = null;
        $cost = 2;

        if(isset($options['algo'])){
            foreach(hash_algos() as $index => $algo){
                if($algo == $options['algo']) {
                    $algorithm = $algo;
                    break;
                }
            }
        }

        if(!$algorithm){
            $index = 3;
            $algorithm = hash_algos()[3];
        }

        if(isset($options['cost'])){
            if($cost<10 && $cost>0) {
                $cost = $options['cost'];
            }
        }

        $spice = self::zehash_sauce(uniqid(), uniqid(), 1, $algorithm);
        if(strlen($spice) > 24) $spice = substr($spice, 0, 24);
        $sauce = self::zehash_sauce($string, $spice, $cost*$cost, $algorithm);

        return array(
            "string" => $string,
            "algorithm" =>$algorithm,
            #"salt" => $salt,
            "hash" => $index."-".$cost."-".$spice."-".$sauce
        );
    }

    public static function zehash_sauce($string, $salt, $cost=2, $algo="sha1"){
        $spice = hash($algo, $salt);
        $sauce = hash($algo, $string.$spice);

        for($i=0; $i<$cost; $i++){
            $spice .= hash($algo, $sauce);
            $sauce = hash($algo, $sauce.$spice);
        }

        return $sauce;
    }

    /**
     * ZeHash Verify
     * verify string hash
     */
    public static function zehash_verify($string, $hash){
        if(empty($string) || empty($hash)) return ["verifies" => false];

        $frags = explode("-", $hash);
        $algo = hash_algos()[$frags[0]];
        $cost = (int) $frags[1];
        $spice = $frags[2];
        $sauce = self::zehash_sauce($string, $spice, $cost*$cost, $algo);

        return [
            "string" => $string,
            "hash" => $hash,
            #"algorithm" => $algo,
            #"cost" => $cost,
            #"spice" => $spice,
            "verifies" => ($sauce == $frags[3])
        ];
    }
}
