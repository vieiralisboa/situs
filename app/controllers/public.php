<?php

/**
 * Public controller
 */
class Public_Controller {
    public function get($request) {
        $root = "/mnt/Public";

        // Routes
        switch($request->uri[1]){
            case "serve":
                $file = $root.getPathname($request, 1);
                return Util::serve($file);

            case "play":
                $file = $root.getPathname($request, 1);
                return Util::rangeDownload($file);

            default:
                Router::$json = false;
                return browse($root, $request);
        }
    }
}

function browse($root, $request){
    $url = getPathname($request, 0);
    $folder = $root.$url;

    $style = "<style>";
    $style .= "\nbody {background-color:black;color:silver;}";
    $style .= "\na.file {color: rgb(160,240,200); text-decoration:none;}";
    $style .= "\na, a.folder {color: rgb(200,160,240);text-decoration:none;}";
    $style .= "\na:hover {text-decoration: none; text-shadow: 0 0 5px black}";
    $style .= "\nol {display: inline-block; padding:0px; margin: 0 0 0 10px}";
    $style .= "\nli {display:inline-block; line-height: 24px; padding: 3px 12px; margin:0px; border-radius: 12px;-webkit-transition: .25s; transition: .25s;}";
    $style .= "\nli:hover {background-color: rgba(133,133,133,.25);}";
    $style .= "\nli * {vertical-align: middle;}";
    $style .= "\nh3, h3 a {color: rgb(90,90,90);}";
    $style .= "\nh3 a:hover {color: rgb(140,140,140);;";
    $style .= "\n</style>";

    $back = dirname(getPathname($request, -1));
    $back = ($back == "/")? "":
        "\n<li><img src=\"/img/005_55.png\"> <a href=\"".$back."\">..</a></li>";
    $folders = $files = "";

    foreach(listFolder($folder) as $file){
        $info = pathinfo($file);
        switch(strtolower($info['extension'])){
            case "mp4":
                $files .= "\n<li><img src=\"/img/005_47.png\"> <a class=\"file\" href=\"/{$request->uri[0]}/play".$url."/".$file."\">";
                $files .= $info['basename']."</a></li>";
                break;

            case "mp3":
            case "jpg":
            case "jpeg":
            case "epub":
            case "pdf":
                $a = "<a class=\"file\" href=\"/{$request->uri[0]}/serve".$url."/".$file."\">".$info['basename']."</a>";
                $files .= "\n<li><img src=\"/img/005_47.png\"> $a</li>";
                break;

            default:
                if(is_dir($folder."/".$file)){
                    $folders .= "\n<li><img src=\"/img/005_43.png\"> <a class=\"folder\" href=\"".getPathname($request, -1)."/".$info['basename']."\">";
                    $folders .= $info['basename']."</a></li>";
                }
        }
    }

    $crums = "<h3>".getCrums($request)."</h3>";
    $html = "<ol>$crums $back$folders$files\n</ol>";
    $html = "<pre>\n$html\n</pre>";
    $html = "<body>\n$style\n$html\n</body>";
    $html = "<html>\n$html\n</html>";

    return "<!doctype html>\n$html";
}

function getCrums($request){
    $sep = "/";
    $k = 0;

    $crum = $sep.$request->uri[$k];
    $crums = "/<a href=\"". $crum ."\">". $request->uri[$k++]."</a>";

    while(isset($request->uri[$k])){
        if(!isset($request->uri[$k+1])){
            $crums .= "/".$request->uri[$k];
            break;
        }

        $crum .= $sep.$request->uri[$k];
        $crums .= "/<a href=\"". $crum ."\">". $request->uri[$k++]."</a>";
    }

    return urldecode(str_replace("+", "%2B", $crums));
}

function getPathname($request, $i=0){
    $sep = "/";
    $k = $i;
    $path = $sep.$request->uri[++$k];
    while(isset($request->uri[++$k]))
        $path .=  $sep.$request->uri[$k];

    return urldecode(str_replace("+", "%2B", $path));
}

function listFolder($dir) {
    $dir = rtrim($dir, '\\/');

    $result = array();
    $h = opendir($dir);

    while (($f = readdir($h)) !== false) {
        if ($f !== '.' and $f !== '..') {
            if ( $f[0] != ".") $result[] = $f;
        }
    }

    closedir($h);
    return $result;
}
