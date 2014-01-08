<?php
/**
 * Toolbar
 */
class Situs52_Controller {
	public function get() {
		//return "disabled";
		$LIBS = "/shares/www/libs/";
		
		switch( true )
		{
			//
			// 
			//
			case $request = Util::preg_match_uri("/situs"):
				//return $request;
				#return "'/situs'";

				Util::quit(404);
			
			//
			// http://situs.sítio.pt/situs/bar/VideoPlayer
			//
			case $request = Util::preg_match_uri('/situs/bar/:script'):
				$script = $request['script'];
				$file = $LIBS . "jquery.bar/js/bar.$script.js"; 
				if(file_exists($file)) return Util::download($file);

			//
	        // Toolbar :script
	        //
			case $request = Util::preg_match_uri('/situs/js/:script'): 
				//return $request;
				#return "'/situs/js/:script'";

				$script = $request['script'];
				$base = $LIBS . "frontgate/";
				switch($script)
				{
					case "frontgate":
					case "frontgate.js":
						$filename = $base."js/frontgate.js";
						break;
					default:
						$filename = $base."js/$script";
				}

				//return $filename;
				if(file_exists($filename)) return Util::download($filename);
				//else Util::quit(404);		

			//
			//
			//
			default: 
				// situs/<$match[1]>?<$match[3]>
				preg_match("/situs\/js\/([\w\.\$]*)(\?([\w\&\=\-\.\_]*))?/", 
					$_SERVER['REQUEST_URI'], $matches);
				$name = $matches[1];
				$query = query($matches);
				$files = array();
				
				//
				// situs/js/<$name>?<$query>
				//
				switch($name){

					//
					// situs/bar?<$query>
					//
					case "bar":
						//return $request;
						#return "'/situs/js/bar?:query'";

						foreach($query as $name => $value) {
							#$file = $LIBS . "jquery.bar/js/min-bar.$name.js";
							$file = $LIBS . "jquery.bar/js/bar.$name.js";

							if(file_exists($file)) return Util::download($file);
						}
						break;
					
					//
					// situs/frontgate?<$query>
					//
					case "frontgate":
						#$temp_file = $LIBS . "TEMP/min-frontgate";
						$temp_file = $LIBS . "TEMP/frontgate";

						$files = frontgate($matches, $LIBS, $temp_file);

						$temp_file .= ".js";//".". intval(time()/86400) . 		
						break;

					default:

				}

				if(count($files)) {	
					$script = "";
					
					if(file_exists($temp_file)) //unlink($temp_file);
						return Util::download($temp_file);
					
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

function query($matches)
{
	$params = array();

	if(isset($matches[3]))
	{
		$sets = explode("&", $matches[3]);

		for ($i=0; $i < count($sets); $i++) 
		{
			$param = explode("=", $sets[$i]);

			if(!isset($param[1])) $param[1] = null;
			$params[$param[0]] = $param[1];
		}
	}
	return $params;
}

function frontgate($matches, $LIBS, &$temp_file){
	$files = array();	
	$requires = array(
		"situs" => array(
			"underscore/1.4.2/underscore-min.js",
			"jquery-ui/jquery-ui-1.10.2.custom/js/jquery-ui-1.10.2.custom.min.js",
			"topzindex/1.2/jquery.topzindex.min.js",
			#"jquery.panel/min-jquery.panel.js",
			"jquery.panel/jquery.panel.js",
			#"jquery.bar/js/min-bar.js",
			"jquery.bar/js/bar.js"
		),

		"router" => array(
			"underscore/1.4.2/underscore-min.js"
		)
	);
	#$files[] = $LIBS . "frontgate/js/min-frontgate.js";
	$files[] = $LIBS . "frontgate/js/frontgate.js";

	if(count($matches) > 2)	{
		$names = explode("&", $matches[3]);
		foreach($names as $name) {
			#$file = $LIBS . "frontgate/js/min-frontgate.$name.js";
			$file = $LIBS . "frontgate/js/frontgate.$name.js";

			if(file_exists($file)){
				$temp_file .= "&" . $name;
				if(isset($requires[$name])) {
					foreach($requires[$name] as $require) {
						if(!in_array($require, $files)) {
							$files[] = $LIBS . $require;
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