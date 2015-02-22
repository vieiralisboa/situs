<?php

/**
 * myTV controller
 */
class MyTV_Controller
{

    public function get($request)
    {
        if($request->uri[1] == 'auth') return "Authorized";

        $config = Router::$controller_config;
        $sep = "/";
        $k = 1;

        // Routes
        switch($request->uri[1]) {

            // Route /myTV/config
            case 'config':
                return $config;

            // Route /myTV/dir/$dir
            case 'dir':
                if(!isset($request->uri[$k+1])) return false;

                $path = $config->path;
                $dir = $root = $request->uri[++$k];
                while(isset($request->uri[++$k]))
                    $dir .=  $sep.$request->uri[$k];
                $path .= $sep.$dir;

                if($root == "transmission") {
                    $path = $config->transmission;
                }

                $videos = $_list = array();

                if(file_exists($path) && is_dir($path)) {
                    foreach(Util::listR($path) as $_file) {
                        if(preg_match('/\.mp4$/', $_file)) {
                            if($root != "tv-shows") {
                                // add the filename, to be sure not to overwrite a array key
                                $_ctime = filectime($path.$sep.$_file) . ',' . $_file;
                                $_list[$_ctime] = $_file;
                            }
                            else {
                                $_ctime = $_file;
                                $_list[$_ctime] = $_file;
                            }
                        }
                    }

                    if($root != "tv-shows") krsort($_list);
                    else sort($_list);
                }

                foreach($_list as $video) {
                    $info = pathinfo($video);
                    $show = array();
                    
                    $video = str_replace("#", "%23", htmlentities($video));

                    #$show['info'] = $info;
                    #$show['root'] = $root;

                    $show['mp4'] = 'video'.$sep.$dir.$sep.$video;
                    $show['name'] = name($info, $root);
                    $show['poster'] = poster($info, $root, $config, $dir);

                    // vtt Subtitles
                    if(file_exists($path.$sep.$info['filename'].".PT.srt")){
                        $show['vtt'] = "/VideoPlayer/vtt/?sub=".$dir.$sep.$info['filename'].".PT.vtt";
                    }

                    // add video to the list
                    $videos[] = $show;
                }

                return $videos;

            default:
                return false;
        }
    }
}

function name($info, $root)
{
    $name = $info['filename'];
    if($root == "tv-shows" || $root == "NEW"){
        $pattern = "/^(.*)[\.| |_](s\d{1,2}e\d{1,2})/i";
        $result = preg_match($pattern, $name, $matches);
        if($result){
            $series = str_replace('.', " ", $matches[1]);
            $name = "$series {$matches[2]}";
        }
        return $name;
    }

    return str_replace('.', " ", $name);
}

function poster($info, $root, $config, $dir)
{
    $sep = "/";
    if($root == "tv-shows" || $root == "NEW") {
        $name = $info['filename'];
        $pattern = "/^(.*)[\.| |_](s\d{1,2}e\d{1,2})/i";
        $result = preg_match($pattern, $name, $matches);

        if($result){
            $series = str_replace('.', " ", $matches[1]);
            $folder = str_replace(' ', "_", strtolower($series));
            $file = "tv-shows/$folder$sep$folder.jpg";

            if(file_exists($config->path.$sep.$file)){
                return "poster/$file";
            }
        }
    }
    else {
        $path = $config->path.$sep.$dir;
        if(file_exists($path.$sep.$info['filename'].".jpg")) {
            return "poster/".$dir.$sep.$info['filename'].".jpg";
        }
    }

    return false;
}
