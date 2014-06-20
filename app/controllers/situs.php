<?php
/**
 * Toolbar
 */
class Situs_Controller {
	
	public function get() {//return "disabled";
		$HTDOCS = "/shares/jlisboa/WD SmartWare.swstor/ULTRABOOK/Volume.7d313caf.1c93.4c09.838c.44ab9c4ba2a0/htdocs/";

		switch( true ){
			//
			case $request = Util::preg_match_uri("/situs")://return $request;
				Util::quit(404);

			case $request = Util::preg_match_uri('/situs/bar/:script'):
				$script = $request['script'];
				$file = $HTDOCS . "sites/ze/js/bar/js/bar.$script.js";
				if(file_exists($file)) return Util::download($file);

			case $request = Util::preg_match_uri('/situs/js/:script')://return $request;
				$script = $request['script'];
				$base = $HTDOCS . "sites/frontgate/public/";
				switch($script){
					case "frontgate":
					case "frontgate.js":
						$filename = $base."js/frontgate.js";
						break;
					default:
						$filename = $base."js/$script";
				}

				if(file_exists($filename)) {
					return Util::download($filename);
				}

			default: //return "enter default";

				// situs/<$match[1]>?<$match[3]>
				preg_match("/situs\/js\/([\w\.]*)(\?([\w\&\=\-\.\_\p{L}]*))?/u",
					urldecode($_SERVER['REQUEST_URI']), $matches);
				$name = $matches[1];
				$query = query($matches);
				$files = array();

				// situs/js/<$name>?<$query>
				switch($name){

					// situs/bar?<$query>
					case "bar":#return $query;
						foreach($query as $name => $value) {
							$filename = "bar.$name.js";
							$script = "bar/bar.$name.js";
							$file = utf8_decode($HTDOCS."sites/ze/js/bar/js/bar.$name.js");

							//DEVELOPMENT attach the filename
							$temp_file = "/shares/www/tmp/" . $filename;
							if(file_exists($file)) {
								$body = file_get_contents($file);
							}
							//else return $file;

							$protocol = $_SERVER['HTTPS']? "https" : "http";
							$file2 = explode("/", $file);
							$file0 = $file2[count($file2)-1];

$SCRIPT = <<<SCRIPT
//Situs_Controller>>>
window.BAR_JSON = "{$script}on";
window.BAR_NAME = "$name";
(function(FILE){

//JavaScript $file
$body
//Situs_Controller>>>
})({
    name: "$name",
    filename: "$file0",
    script: "$script",
    path: "$file",
    url: "$protocol://{$_SERVER['SERVER_NAME']}{$_SERVER['REQUEST_URI']}"
});

SCRIPT;

						file_put_contents($temp_file, $SCRIPT);
						if(file_exists($temp_file)) return Util::download($temp_file);
						else return $temp_file;

						if(file_exists($file)) return Util::download($file);
					}

					break;

				// situs/frontgate?<$query>
				case "frontgate":
					$temp_file = "/shares/www/tmp/frontgate"; 
					$files = frontgate($matches, $HTDOCS, $temp_file);
					$temp_file .= ".js";//".". intval(time()/86400)
					break;

				// situs/<$name>?<$query>
				default:

			}

			if(count($files)) {
				$script = "";

				if(file_exists($temp_file))
					unlink($temp_file);

				foreach($files as $file)
					$script .= "\n" . file_get_contents($file);

				file_put_contents($temp_file, $script);

				if(file_exists($temp_file))
					return Util::download($temp_file);
			}

			Util::quit(404);//return floatval(phpversion());
		}
	}
}

function query($matches){
	$params = array();
	if(isset($matches[3])){
		$sets = explode("&", $matches[3]);
		for ($i=0; $i < count($sets); $i++){
			$param = explode("=", $sets[$i]);
			$params[$param[0]] = $param[1];
		}
	}
	return $params;
}

function frontgate($matches, $HTDOCS, &$temp_file){
	$files = array();	
	$requires = array(
		"situs" => array(
			"libs/underscore/1.4.2/underscore-min.js",
			"libs/jquery-ui/jquery-ui-1.10.2.custom/js/jquery-ui-1.10.2.custom.js",
			"libs/topzindex/1.2/jquery.topzindex.js",
			"sites/ze/js/panel/panel.js",
			"sites/ze/js/bar/js/bar.js"
		),
		"router" => array(
			"libs/underscore/1.4.2/underscore-min.js"
		)
	);

	$LIB = $HTDOCS  ."sites/frontgate/public/";
	$files[] = $LIB . "js/frontgate.js";

	if(count($matches) > 2)	{
		$names = explode("&", $matches[3]);
		foreach($names as $name) {
			$file = $LIB . "js/frontgate.$name.js";
			if(file_exists($file)){
				$temp_file .= "&" . $name;
				if(isset($requires[$name])) {
					foreach($requires[$name] as $required) {
						if(!in_array($required, $files)) {
							$files[] = $HTDOCS . $required;
						}
					}	
				}
				$files[] = $file;
			} 
		}
	}

	return $files;
}

/*
$CASH = false;
$MIN = false;
foreach($query as $name => $value){
	if($name = "cash") 
	{
		$CASH = true;
		unset($query['cash']);
	} 
	if($name = "min") 
	{
		$MIN = true;
		unset($query['min']);
	}
}
if(isset($query['_'])) unset($query['_']);

if($CASH && file_exists($temp_file))
{
	 return Util::download($temp_file);
}

// Minify javascript file
//---------------------------------------------------------				
if($MIN){
	require $LIBS . 'JShrink/src/JShrink/Minifier.php';
	$script = "//situs.pt/#!Frontgate" . Minifier::minify($script, 
		array('flaggedComments' => false));
}
//---------------------------------------------------------			
*/