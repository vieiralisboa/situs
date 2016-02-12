<?php

function response($request, $root)
{
    // Routes
    switch($request->uri[1]) {
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

function browse($root, $request)
{
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
    $style .= "\nli span{cursor: default; color: gray;}";
    $style .= "\nh3, h3 a {color: rgb(90,90,90);}";
    $style .= "\nh3 a:hover {color: rgb(140,140,140);}";
    $style .= "\n</style>";

    $back = dirname(getPathname($request, -1));
    $back = ($back == "/")? "":
        "\n<li><img src=\"/img/005_55.png\"> <a href=\"".$back."\">..</a></li>";
    $folders = $files = "";

    foreach(listFolder($folder) as $file) {
        
        $info = pathinfo($file);
        $filesize = getSize("$folder/$file");

        switch(strtolower($info['extension'])) {
            case "mp4":
                $href = $request->uri[0]."/play".$url."/".$file;
                $href = str_replace("#", "%23", $href);
                $files .= "\n<li><img src=\"/img/005_47.png\"> <a class=\"file\" href=\"/$href\">";
                $files .= $info['basename']."</a> <span>$filesize</span></li>";
                break;
            case "mp3":
            case "jpg":
            case "jpeg":
            case "epub":
            case "pdf":
                $href = $request->uri[0]."/serve".$url."/".$file;
                $href = str_replace("#", "%23", $href);
                $a = "<a class=\"file\" href=\"/$href\">".$info['basename']."</a>";
                $files .= "\n<li title=\"$folder/$file\"><img src=\"/img/005_47.png\"> $a <span>$filesize</span></li>";
                break;
            default:
                if(is_dir($folder."/".$file)){
                    $href = getPathname($request, -1)."/".$info['basename'];
                    $href = str_replace("#", "%23", $href);
                    $folders .= "\n<li><img src=\"/img/005_43.png\"> <a class=\"folder\" href=\"$href\">";
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


function getSize($file)
{
    $filesize = filesize($file);

    if($filesize < 1000) {
        return "$filesize Bytes";
    }
    else if($filesize < 1000000) {
        $filesize = round($filesize/1000, 2);
        return "$filesize KB";
    }
    else if($filesize < 1000000000) {
        $filesize = round($filesize/1000000, 2);
        return "$filesize MB";
    }
    else {
        $filesize = round($filesize/1000000000, 2);
        return "$filesize GB";   
    }
}

function getCrums($request)
{
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

function getPathname($request, $i=0)
{
    $sep = "/";
    $k = $i;
    $path = $sep.$request->uri[++$k];
    while(isset($request->uri[++$k]))
        $path .=  $sep.$request->uri[$k];

    return urldecode(str_replace("+", "%2B", $path));
}

function listFolder($dir)
{
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
