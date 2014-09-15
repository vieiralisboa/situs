<?php

/**
 * myTV controller
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
                $dir = $root = $request->uri[++$k];
                while(isset($request->uri[++$k]))
                    $dir .=  $sep.$request->uri[$k];
                $path .= $sep.$dir;

                $videos = $_list = array();

                if(file_exists($path)) {
                    $_dir = opendir($path);
                    while($_file = readdir($_dir)){
                        if ($_file != '.' and $_file != '..'){
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
                    closedir($_dir);
                    if($root != "tv-shows") krsort($_list);
                    else sort($_list);
                }

                foreach($_list as $video){
                    $info = pathinfo($video);

                    if(preg_match('/\.mp4$/', $video)) {
                        $show = array();

                        $show['mp4'] = 'video'.$sep.$dir.$sep.$info['filename'];
                        $show['name'] = str_replace('.', " ", $info['filename']);

                        // Subtitles (vtt)
                        if(file_exists($path.$sep.$info['filename'].".PT.srt")){
                            $show['vtt'] = "/VideoPlayer/vtt/?sub=".$dir.$sep.$info['filename'].".PT.vtt";
                        }

                        // Poster
                        if($root == "tv-shows" || $root == "NEW"){
                            $name = $info['filename'];
                            $pattern = "/^(.*)[\.| |_](s\d{1,2}e\d{1,2})/i";
                            $result = preg_match($pattern, $name, $matches);

                            if($result){
                                $series = str_replace('.', " ", $matches[1]);
                                $folder = str_replace(' ', "_", strtolower($series));

                                $name = "$series {$matches[2]}";
                                $file = "tv-shows/$folder$sep$folder.jpg";

                                if(file_exists($config->path.$sep.$file)){
                                    $show['poster'] = "poster/$file";
                                }
                                else $show['poster'] = false;
                            }
                            else $show['poster'] = false;

                            $show['name'] = $name;

                        }
                        else {
                            if(file_exists($path.$sep.$info['filename'].".jpg"))
                                $show['poster'] = "poster/".$dir.$sep.$info['filename'].".jpg";
                            #elseif (file_exists($path.$sep.$info['filename'].".png"))
                            #    $show['poster'] = "poster/".$dir.$sep.$info['filename'].".png";
                            else $show['poster'] = false;
                        }

                        // add video to the list
                        $videos[] = $show;
                    }
                }
                return $videos;

            default:
                return false;
        }
    }
}
