<?php
error_reporting(0);

/**
 * Toolbar
 */
class Situs_Controller {

	public function get() {//return "disabled";

		$config = Router::$controller_config;

		switch( true ){
			// BASE
			case $request = Util::preg_match_uri("/situs")://return $request;
				Util::quit(404);

			// BAR
			case $request = Util::preg_match_uri('/situs/bar/:script'):
				$script = $request['script'];
				$file = $config->htdocs . $config->bar . "js/bar.$script.js";
				if(file_exists($file)) return Util::download($file);

			// SCRIPT
			case $request = Util::preg_match_uri('/situs/js/:script')://return $request;
				$script = $request['script'];
				$base = $config->htdocs . $config->frontgate;
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
							$script = "jquery.bar/js/bar.$name.js";
							$file = utf8_decode($config->htdocs.$config->bar . "js/bar.$name.js");

							//DEVELOPMENT attach the filename
							$temp_file = $config->temp.$filename;
							if(file_exists($file)) {
								$body = file_get_contents($file);
								$json = file_get_contents($file."on");
								if($json) $json = json_encode(json_decode($json));
								else $json = json_encode(null);
							}
							//else return $file;

							$protocol = $_SERVER['HTTPS']? "https" : "http";
							$file2 = explode("/", $file);
							$file0 = $file2[count($file2)-1];

$SCRIPT = <<<SCRIPT
//Situs_Controller>>>
(function(FILE){

//JavaScript $file
$body
//Situs_Controller>>>
})({
    name: "$name",
    filename: "$file0",
    script: "$script",
    path: "$file",
    url: "$protocol://{$_SERVER['SERVER_NAME']}{$_SERVER['REQUEST_URI']}",
    json: '$json'
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
					$temp_file =  $config->temp . "frontgate"; 
					$files = frontgate($matches, $config, $temp_file);
					$temp_file .= ".js";//".". intval(time()/86400)

					break;

				// situs/<$name>?<$query>
				default:
			}

//return $config;
//return $files;

			if(count($files)) {
				$script = "";

				if(file_exists($temp_file)) unlink($temp_file);

				foreach($files as $file)
					$script .= "\n" . file_get_contents($file);

				file_put_contents($temp_file, $script);

				if(file_exists($temp_file))
					return Util::serve($temp_file);
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

function frontgate($matches, $config, &$temp_file){

	$files = array();	
	$requires = array(
		"situs" => array(
			// http://docs.medorc.org/
			"underscore/1.4.2/underscore-min.js",
			"jquery-ui/jquery-ui-1.10.2.custom/js/jquery-ui-1.10.2.custom.js",
			"topzindex/1.2/jquery.topzindex.js",
			"jquery.panel/panel.js",
			"jquery.bar/js/bar.js"
		)//,
		//"router" => array(
		//	"underscore/1.4.2/underscore-min.js"
		//)
	);

	// 
	$files[] = $config->frontgate . "frontgate.js";

	if(count($matches) > 2)	{
		$names = explode("&", $matches[3]);

//return $names;

		for($i=0; $i<count($names); $i++){
			$name = $names[$i];
		//foreach($names as $name) {
			$file = $config->frontgate . "frontgate.$name.js";
			if(file_exists($file)){
				$temp_file .= "&" . $name;
				if(isset($requires[$name])) {
					foreach($requires[$name] as $required) {
						if(!in_array($required, $files)) {
							$files[] = $config->libs . $required;
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