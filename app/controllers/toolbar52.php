<?php
/**
 * Toolbar
 */
class Toolbar52_Controller {
	
	public function get() {
		
		switch( true )
		{
			//
			// Get All Items
			//
			case $request = Util::preg_match_uri("/toolbar"):
				//return $request;
				//Util::quit(404);
				
			//
	        // Get Toolbar :script
	        //
			case $request = Util::preg_match_uri('/toolbar/js/:script'): 
				//return $request;
				$script = $request['script'];
				$name = str_replace('.js', '', $script);
				
				$base = "/shares/www/libs/";
				
				switch($script){
					case "toolbar.js":
						$filename = $base."jquery.toolbar/js/min-toolbar.js";
						break;
					case "start.js":
						$filename = $base."hosts/situs.pt/js/toolbar.start.js";
						break;
					case "webDevelopment.js":
						$filename = $base."hosts/situs.pt/js/toolbar.webDevelopment.js";
						break;
					default:
						$filename = $base."jquery.toolbar.$name/js/min-toolbar.$script";
				}

				//return $filename;
				if(file_exists($filename)) return Util::download($filename);
				//else Util::quit(404);		
						
			default: Util::quit(404);//return floatval(phpversion());

		}
	}
}

