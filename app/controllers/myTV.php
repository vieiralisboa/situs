<?php

/**
 * Upload/Stream Video
 * TODO seperate controllers for folder contents and download_video
 */
class MyTV_Controller {

    public function get($request) {
        if($request->uri[1] == 'auth') return "Authorized";

        $config = Router::$controller_config;
        $sep = "/";//"\\";
        $k = 1;

        // Routes
        switch($request->uri[1]){

            // Route /myTV/config
            case 'config':
                return $config;

            // Route /myTV/dir/$dir
            case 'dir':
                if(!isset($request->uri[$k+1])) return false;
                
                $path = $config->path;
                $dir = $request->uri[++$k];
                while(isset($request->uri[++$k]))
                    $dir .=  $sep.$request->uri[$k];
                $path .= $sep.$dir;

                $videos = array();
                foreach(scandir($path) as $video){
                    $info = pathinfo($video);
                    if(preg_match('/\.mp4$/', $video)) {
                        $name = $info['filename'];
                        $name = preg_replace("/\.hdtv.*/i", "", $name);
                        $name = preg_replace("/\.webrip.*/i", "", $name);
                        $name = preg_replace("/\.web\-dl.*/i", "", $name);
                        $name = str_replace('.', " ", $name);
                        $show = array(
                            'name' => $name,// $info['basename'],
                            'mp4' => 'show'.$sep.$dir.$sep.$info['filename']
                            //'mp4' => "http://guest:guest@situs.no-ip.org:8080/myTV/show/" . $dir."/".$info['filename']
                        );

                        // Subtitles (vtt)
                        if(file_exists($path.$sep.$info['filename'].".vtt"))
                            $show['vtt'] = "/VideoPlayer/vtt/?sub=".$dir.$sep.$info['filename'].".vtt";

                        // add video to the list
                        $videos[] = $show;
                    }
                }
                return $videos;
            
            // Route API/myTV/vtt/$vtt
            case 'vtt':
                // requested subtitle
                $vtt = $config->path;
                while(isset($request->uri[++$k]))
                    $vtt .= $sep.$request->uri[$k];
                
                if(!file_exists($vtt)) return false;

                //Util::serve($vtt);
                return Util::download($vtt);

            // Route /myTV/show/$file
            case 'show':
                // path to the file's folder
                $file = $config->path;
                while(isset($request->uri[++$k]))
                    $file .= $sep.$request->uri[$k];
                    
                //$filename = $request->uri[$k];

                // requires curl
                #if(file_exists($file)) mp4Upload($file);

                // requested video // only stream partial video (range)
                if(!file_exists($file) || empty($_SERVER['HTTP_RANGE']))
                    return false;

                rangeDownload($file);// stream the video
                exit;

            default:
                return false;
        }
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
    exit;
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
