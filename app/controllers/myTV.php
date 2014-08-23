<?php

/**
 * Upload/Stream Video
 * TODO seperate controllers for folder contents and download_video
 */
class MyTV_Controller {

    public function get($request) {
        
        if($request->uri[1] == 'auth') return "Authorized";

        //DEBUG
        //return $request;

        $config = Router::$controller_config;


        // folder containing the videos
        $path = $config->path;
        $sep = "/";//"\\";

        // valid requests contain 
        if(count($request->uri) < 3) return;

        // Route 1. /myTV/dir/:dir
        //---------------------------------------------------------------------
        $k = 1;
        if($request->uri[$k] == 'dir' && isset($request->uri[$k+1])){
            $dir = $request->uri[++$k];
            
            while(isset($request->uri[++$k])){
                $dir .=  $sep.$request->uri[$k];
            }

            $path .= $sep.$dir;
            //return $path;

            //$url = "http://situs.no-ip.org:8080/myTV/show/";
            $videos = array();
            foreach(scandir($path) as $video){
                $info = pathinfo($video);
                if(preg_match('/\.mp4$/', $video)) 
                {
                    $name = $info['filename'];
                    $name = preg_replace("/\.hdtv.*/i", "", $name);
                    $name = preg_replace("/\.webrip.*/i", "", $name);
                    $name = preg_replace("/\.web\-dl.*/i", "", $name);
                    $name = str_replace('.', " ", $name);
                    $show = array(
                        'name' => $name,// $info['basename'],
                        'mp4' => 'show' . $sep . $dir. $sep . $info['filename']
                        //'mp4' => "http://guest:guest@situs.no-ip.org:8080/myTV/show/" . $dir."/".$info['filename']
                    );

                    // HD (m4v)
                    //---------------------------------------------------------
                    $m4v = $path . $sep . $info['filename'] . ".m4v";
                    if(file_exists($m4v)) $show['m4v'] = true;
                    //else $show['m4v'] = false;

                    // Subtitles (vtt)
                    //---------------------------------------------------------
                    // vtt file exists
                    if(file_exists($path.$sep.$info['filename'].".vtt"))
                    {
                        // include vtt file (where the vtt file was uploaded)
                        $show['vtt'] = vtt($info['filename'].".vtt", $path);
                    }

                    // add video to the list
                    $videos[] = $show;
                }
            }
            return $videos;
        }

        // Route 2. /myTV/show/:folder/:file
        //---------------------------------------------------------------------
        $i = 1;
        if($request->uri[$i] == 'show'){
            // DEBUG
            //return $request;

            $file = $path;

            // path to the file's folder
            while(isset($request->uri[++$i])){
                $file .= $sep.$request->uri[$i];
            }

            $filename = $request->uri[$i];// . ".mp4";

            // requested video
            if(!file_exists($file)) return false;

            // only stream partial video (range)
            if(empty($_SERVER['HTTP_RANGE'])) {
                // upload video
                #mp4Upload($file);
                //file_put_contents("myTV_0_SERVER.json", json_encode($_SERVER));
                return false;
            }

            //file_put_contents("myTV_1_SERVER.json", json_encode($_SERVER));

            // stream the video
            rangeDownload($file);

            exit;
        }

        // Invalid route
        //---------------------------------------------------------------------
        return false;
    }  
}

function mp4Upload($mp4){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $mp4);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $out = curl_exec($ch);
    curl_close($ch);

    header('Content-type: video/mp4');
    header('Content-type: video/mpeg');
    header('Content-disposition: inline');
    header("Content-Transfer-Encoding:­ binary");
    header("Content-Length: ".filesize($out));
    echo $out;
    exit();
}

/**
 * Download file range
 */
function rangeDownload($file) {
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

function vtt($vtt, $path) {
    $header = 'Content-type: application/x-www-form-urlencoded';
    $json =  $path."/".'SENT.situs.json';
    $sent_subs = file_exists($json)? json_decode(file_get_contents($json)): array();
    
    /*/ALTERNATIVE1 post vtt file to the server                   
    $sent = $vtt.'.SENT';
    if(!file_exists($sent))//*/
    #$sent = $path."/".'SENT.situs.txt';

    if(!in_array($vtt, $sent_subs)){
        $sent_subs[] = $vtt;
        file_put_contents($json, json_encode($sent_subs));

        //$url = 'http://situs.pt/vtt/post.php';
        $url = 'http://xn--stio-vpa.pt/VideoPlayer/post.php';
        $postdata = http_build_query(array(
            'name' => $vtt,
            'text' => file_get_contents($path.'/'.$vtt)
        ));

        $opts = array('http' => array(
            'method'  => 'POST',
            'header'  => $header,
            'content' => $postdata
        ));

        //file_put_contents("C:\\TEMP\\req.json", json_encode($opts));

        /*/ALTERNATIVE1 update sent list
        file_put_contents($sent, "uploaded to " . $url);//*/
        #file_put_contents($sent, $vtt."\n", FILE_APPEND);

        $context  = stream_context_create($opts);
        $result = file_get_contents($url, false, $context);   
    }


    /*ALTERNATIVE to post vtt (untested)
    $params = array('http' => array(
        'method' => 'POST',
        'content' => 'toto=1&tata=2'
    ));
    $ctx = stream_context_create($params);
    $fp = @fopen($sUrl, 'rb', false, $ctx);
    if (!$fp)
    {
        throw new Exception("Problem with $sUrl, $php_errormsg");
    }
    $response = @stream_get_contents($fp);
    if ($response === false) 
    {
        throw new Exception("Problem reading data from $sUrl, $php_errormsg");
    }
    */
    
    // include vtt file (where the vtt file was uploaded)
    return "/VideoPlayer/" . $vtt;
}