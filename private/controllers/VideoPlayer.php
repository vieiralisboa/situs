<?php

/**
 * Upload/Stream Video
 * TODO seperate controllers for folder contents and download_video
 */
class VideoPlayer_Controller
{

    public function get($request)
    {
        $config = Router::$controller_config;

        // Routes
        switch($request->uri[1]) {

            case 'popup':
                $file = $config->popup;
                if(!file_exists($file)) Util::quit(404);
                return Util::serve($file);

            // Video poster
            case 'poster':
                $file = get_filename($config->videos, $request->uri);
                if(!file_exists($file)) Util::quit(404);
                return Util::serve($file);

            // Route /VideoPlayer/video/$file
            case 'video':
                if($request->uri[2] == "transmission") {
                    $config->videos = $config->transmission;
                    #$request->uri[2] = "Transmission";
                    array_splice($request->uri, 2, 1);
                }

                $file = get_filename($config->videos, $request->uri);

                // requested video // only stream partial video (range)
                if(!file_exists($file)) {// || empty($_SERVER['HTTP_RANGE']))
                    return false;
                }

                return Util::rangeDownload($file);

            default:;   
        }

        return Router::location("http://xn--stio-vpa.pt/#VideoPlayer");
    }
}

function get_filename($path, $uri)
{
    $k = 1;
    $file = $path;
    while(isset($uri[++$k])) {
        $str = urldecode($uri[$k]);
        $file .= "/$str";
    }
    return $file;
}
