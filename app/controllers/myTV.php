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
                $dir = $request->uri[++$k];
                while(isset($request->uri[++$k]))
                    $dir .=  $sep.$request->uri[$k];
                $path .= $sep.$dir;

                $videos = array();
                $_dir = opendir($path);
                $_list = array();
                while($_file = readdir($_dir)){
                    if ($_file != '.' and $_file != '..'){
                        // add the filename, to be sure not to
                        // overwrite a array key
                        $_ctime = filectime($path .$sep. $_file) . ',' . $_file;
                        $_list[$_ctime] = $_file;
                    }
                }
                closedir($_dir);
                krsort($_list);

                foreach($_list as $video){
                    $info = pathinfo($video);
                    if(preg_match('/\.mp4$/', $video)) {
                        $name = $info['filename'];
                        $name = preg_replace("/\.hdtv.*/i", "", $name);
                        $name = preg_replace("/\.webrip.*/i", "", $name);
                        $name = preg_replace("/\.web\-dl.*/i", "", $name);
                        $name = str_replace('.', " ", $name);
                        $show = array(
                            'name' => $name,// $info['basename'],
                            'mp4' => 'video'.$sep.$dir.$sep.$info['filename']
                            //'mp4' => "http://guest:guest@situs.no-ip.org:8080/myTV/show/" . $dir."/".$info['filename']
                        );

                        // Subtitles (vtt)
                        if(file_exists($path.$sep.$info['filename'].".srt")){
                            $show['vtt'] = "/VideoPlayer/vtt/?sub=".$dir.$sep.$info['filename'].".vtt";
                        }

                        // Poster
                        if(file_exists($path.$sep.$info['filename'].".jpg"))
                            $show['poster'] = "poster/".$dir.$sep.$info['filename'].".jpg";
                        elseif (file_exists($path.$sep.$info['filename'].".png"))
                            $show['poster'] = "poster/".$dir.$sep.$info['filename'].".jpg";
                        else $show['poster'] = false;

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
