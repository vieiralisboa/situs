<?php

function response($request)
{
    $root = $request->dir;
    #inspect($request);

    // Routes
    switch($request->uri[1]) {
        case "serve":
            $file = $root.getPathname($request, 1);
            return Util::serve($file);
        case "mp4":
            $file = $root.getPathname($request, 1);
            return Util::rangeDownload($file);
        case "play": 
            return htmlVideo($request, $root);
        case "vtt":
            $file = $root.getPathname($request, 1);        
            return Util::vtt(Util::srt2vtt($file)); 
        default:
            if(class_exists(Router)) Router::$json = false;
            return browse($root, $request);
    }
}

function browse($root, $request)
{
    $url = getPathname($request, 0);
    $folder = $root.$url;

    $cssfilename = __DIR__."/navigator.css";
    $htmlfilename = __DIR__."/navigator.html"; 

    if(!file_exists($htmlfilename)) return "<pre>404</pre>";
 
    if(file_exists($cssfilename)) $css = file_get_contents($cssfilename);
    else $css = "";

    $back = $folders = $files = "";

    $self = $request->url.$request->uri[0];
    $id= 1;
    $li = "\n<li data-id=\"%d\" title=\"%s\" class=\"%s tile\"><a class=\"%s\" href=\"%s\">%s</a></li>";
    $icon_src = "$self/serve/images/folder.png";
    $icon = "<br/><img title=\"Folder\" style=\"display: none; width: 32px; margin-top: 192px; float: right;\" src=\"$icon_src\"/>";
    
    //
    $div0 = "<div class=\"video\" style=\"background-image: url('%s');\">%s$icon</div>";  

    foreach(listFolder($folder) as $file) {
        $filename = "$folder/$file";
        $info = pathinfo($file);
        $filesize = getSize($filename);

        if(is_dir($filename)) {// DIRECTORY
            // skip folders without videos
            if(filesInFolder($filename, "mp4") == 0) continue; 

            // ignore folders
            $dirname = strtolower($file);
            foreach($request->ignore as $ignore) {
                if($dirname == $ignore) {
                    $ignore = false;
                    break;
                }
            }

            if(!$ignore) continue;

            // TODO if the folder doesn't have a cover, ignore it

            // cover image
            $cover = str_replace("_", ".", "$file.jpg");
            $cover_path = "$root/posters/$cover";
            if(file_exists($cover_path)) $cover_href = "$self/serve/posters/$cover";
            else $cover_href = "$self/serve/images/Folder.jpg";

            $basename = str_replace("_", " ", $info['basename']);

            $href = str_replace("#", "%23", $request->url.getPathname($request, -1)."/".$info['basename']);
            $folders .= sprintf($li, $id++, $basename, "mp4", "file", $href, sprintf($div0, $cover_href, $basename));
            #$folders .= "\n<li class=\"folder\"><img src=\"{$request->url}/img/005_43.png\"> <a class=\"folder\" href=\"$href\">{$info['basename']}</a></li>";
        }
        else {// FILE
            $movie = false;
            $tv_show = false;
            
            // TV SHOW [Ashby.my.love.2015.S01E02.love.me.1080p.web-dl.aac5.1.x264-situs]
            // array(
            // 0   =>  Ashby.my.love.2015.S01E02.fuck.me.1080p
            // 1   =>  Ashby.my.love.2015
            // 2   =>  S01E02
            // 3   =>  love.me
            // 4   =>  1080p
            // )
            // MOVIE [Ashby.my.love.2015.S01E02.1080p.web-dl.aac5.1.x264-situs]
            // array(5
            // 0   =>  Ashby.my.love.2015.1080p
            // 1   =>  Ashby.my.love.2015
            // 2   =>  Ashby.my.love
            // 3   =>  2015
            // 4   =>  1080p
            // )
            $tv_pattern = '/^([\w\.\-]+)\.(S[0-9]{2}E[0-9]{2})\.?([\w\.\-\']+)?\.([1-9][0-9]{2,3}p)/i';
            $movie_pattern = '/^(([\w\.\-]+)\.([1-2][0-9]{3}))\.([1-9][0-9]{2,3}p)/i';
            $poster = $title = "";
            
            preg_match($tv_pattern, $info['filename'], $matches);            
            if(!isset($matches[1])) preg_match($movie_pattern, $info['filename'], $matches); 
            if(isset($matches[1])) {
                $title = str_replace(".", " ", $matches[1]);
                $poster = $matches[1].".jpg";
                if(file_exists("$root/posters/$poster")) $poster = "$self/serve/posters/$poster";
                else $poster = "";    
            }

            // TODO ignore files without a match
            #continue;

            $subtitle = "";
            if(preg_match('/^S[0-9]{2}E[0-9]{2}$/i', $matches[2])) {// matches an episode (tv-show)
                $subtitle = "<br/><span style=\"font-size: 115%; color: white;\">".$matches[2]."</span>";
                $tv_show = true;
            }
            else {// matches a year? (movie)
                $title = str_replace(".", " ", $matches[2]);
                $subtitle = "<br/>".$matches[3];
                $movie = true;  
            } 

            // process file
            switch(strtolower($info['extension'])) {
                case "mp4":
                    $text = "";
                    // video subtitles 
                    $por = file_exists(str_replace(".mp4", ".por.srt", $filename));
                    $eng = file_exists(str_replace(".mp4", ".eng.srt", $filename));
                    
                    $titles = array();

                    if($eng) $titles[] = "English";
                    if($por) $titles[] = "Português";

                    /*
                    $cc_png = $por? "cc-por.png" : "cc.png";
                    if($por || $eng) $cc = "<br/><img title=\"$title0\" style=\"float: right; margin-top: 146px; margin-right: 4px;\" src=\"$self/serve/images/$cc_png\">";
                    else $cc = ""; 
                    */

                    if(count($titles)>0) $cc = "<br/><img title=\"".implode(", ", $titles)."\" style=\"float: right; margin-top: 142px; margin-right: 4px;\" src=\"$self/serve/images/cc.png\">";
                    else $cc = ""; 

                    // href for mp4 video file
                    $href = str_replace("#", "%23", "$self/play$url/$file");
       
                    // text for video pleceholder
                    $title = $title != "" ? $title : $file;
                    $text .= $title;//.$subtitle.$cc;
                    $text .= $subtitle;//.$cc;

                    #if(!empty($matches[3]))
                    $show_name = "<br/>";
                    if($tv_show) $show_name .= str_replace(".", " ", $matches[3]);
                    $text .= "<i style=\"font-size: 90%;\">".$show_name."</i>";

                    $text .= $cc;

                    // video placeholder cover image
                    #if($poster == "") 
                    if(file_exists(str_replace("mp4", "jpg", $filename))) $poster = str_replace("mp4", "jpg", $self."/serve".$url."/".$file);
                    $div_style = $poster != "" ? "background-size: auto 256px; background-image: url('$poster');" :
                        "opacity: 1.00; background-size: auto 240px; background-image: url('$self/serve/posters/video.png');";
                    
                    $div = "<div class=\"video\" style=\"$div_style\">$text</div>";  
                    
                    // append video item
                    $files .= sprintf($li, $id++, $file, "mp4", "file", $href, $div);

                    break;
                case "jpg":
                    // ignore jpg if a mp4 file with the same name exists
                	if(file_exists(str_replace("jpg", "mp4", $filename))) break;
                case "jpeg":
                case "mp3":
                case "epub":
                case "pdf":
                    break; // uncomment to ignore the above files
                    $href = $request->uri[0]."/serve".$url."/".$file;
                    $href = str_replace("#", "%23", $href);
                    $a = "<a class=\"file\" href=\"/$href\">".$info['basename']."</a>";
                    $files .= "\n<li class=\"file\" title=\"$folder/$file\"><img src=\"/img/005_47.png\"> $a <span>$filesize</span></li>";
                    break;
                default:;
            }
        }
    }

    $html = str_replace("/* style */", $css, file_get_contents($htmlfilename));
    $html = str_replace("<!-- crums -->", getCrums($request), $html);
    $html = str_replace("<!-- folders -->", $folders, $html);
    $html = str_replace("<!-- files -->", $files, $html);

    return $html;
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
    $k = 0;
    $crum = $request->uri[$k++];
    $crum_href = "/". $crum;
    $crum = str_replace("?", "", $crum);


    // more than 1 crum
    if(count($request->uri) > 1 ) $crums = "/<a href=\"". $request->url . $crum_href . "\">$crum</a>";
    else $crums = "/" . $crum;
    
    //
    while(isset($request->uri[$k])){
        
        $crum = $request->uri[$k++];
        $crum_href .= "/" . $crum;

        // last crum
        if(!isset($request->uri[$k])){
            $crums .= "/" . $crum;
            break;
        }

        $crums .= "/<a href=\"" . $request->url . $crum_href ."\">$crum</a>";
    }

    return urldecode(str_replace("+", "%2B", $crums));
}

function getPathname($request, $i=0)
{
    $sep = "/";
    $k = $i;
    $path = $sep.$request->uri[++$k];
    while(isset($request->uri[++$k])) $path .=  $sep.$request->uri[$k];
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

    sort($result);

    return $result;
}

function htmlVideo($request, $root)
{
    $htmlfilename = __DIR__."/video.html";
    if(!file_exists($htmlfilename))
        return "<pre>not found (404)</pre>";
    $html = file_get_contents($htmlfilename);

    $href = $request->url."/".$request->uri[0]."/mp4".getPathname($request, 1);
    $file = $root.getPathname($request, 1);
    $info = pathinfo($file);
    $tracks = "";

    $langs = array("eng" => "English", "por" => "Portuguese");

    foreach($langs as $lang => $language) {
        $vtt = $request->pre."/".$request->uri[0]."/vtt".dirname(getPathname($request, 1))."/".$info['filename'].".$lang.srt";
        $srt = $info['dirname']."/".$info['filename'].".$lang.srt";
        $d = $lang == "por" ? "default" : "";
        if(file_exists($srt)) $tracks .= "<track src=\"$vtt\" kind=\"subtitles\" srclang=\"$lang\" label=\"$language\" $d/>";
    }

    $html = str_replace("<!-- source -->", "<source src=\"$href\" type=\"video/mp4\">", $html);
    $html = str_replace("<!-- tracks -->", $tracks, $html);

    if(class_exists("Router")) Router::$json = false;
    return $html;
}

function filesInFolder($folder, $extension) {
    $n = 0;
    foreach(listFolder($folder) as $file) {
        $info = pathinfo($file);
        if(strtolower($info['extension']) == $extension) $n++;
    }
    return $n;
}
