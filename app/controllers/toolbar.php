<?php
/**
 * Toolbar
 */
class Toolbar_Controller {
	
	public function get() {
		//$base = "/htdocs/libs/";
		$base = "/shares/jlisboa/WD SmartWare.swstor/ULTRABOOK/Volume.7d313caf.1c93.4c09.838c.44ab9c4ba2a0/htdocs/libs/";

		switch( true )
		{
			
			//
	        // Get Toolbar :script
	        //
			case $request = Util::preg_match_uri('/toolbar/addon/:script'): 
				// requested script
				$script = $base.$request['script'];
				
				// upload script
				if(file_exists($script)) return Util::download($script);
				
				// script not found
				Util::quit(404);
				break;

			//
	        // Get Toolbar :script
	        //
			case $request = Util::preg_match_uri('/toolbar/js/:script'): 
				//return $request;
				$script = $request['script'];
				//$name = str_replace('.js', '', $script);
								
				switch($script){
					case "toolbar.js":
						$filename = $base."jquery.toolbar/js/toolbar.js";
						break;
					case "start.js":
						$filename = $base."hosts/frontgate.dev/js/toolbar.start.js";
						break;
					case "toolbar.WebDevelopment.js":
						$filename = $base."hosts/frontgate.dev/js/toolbar.webDevelopment.js";
						break;
					default:
						$filename = $base."jquery.toolbar.$name/js/toolbar.$script";
				}

				//return $filename;
				if(file_exists($filename)) return Util::download($filename);
				//else Util::quit(404);		
			
			case $request = Util::preg_match_uri("/toolbar"):
				//return $request;
				//Util::quit(404);
			default: Util::quit(404);//return floatval(phpversion());
		}
	}
}
