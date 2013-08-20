<?php
/**
 * Example for PHP =<5.2
 */
class Example52_Controller {
	
	public function get() {
		
		switch( true )
		{
			//
			// Get All Items
			//
			case $request = Util::preg_match_uri("/example"):
				return $request;
				return Database::all();
			
			//
			// Seed the Table
			//		
			case $request = Util::preg_match_uri('/example/seed'):
				return $request;				
				$todos = array(
	                array('id'=>1, 'title'=>"Go to the supermarket.", 'done'=>1),//, 'time'=>'2013-01-25 12:23:55'
	                array('id'=>2, 'title'=>"Practice guitar.", 'done'=>0),
	                array('id'=>3, 'title' => "Wash the dishes.", 'done'=>0));
	            return Database::seed($todos);
			
			//
	        // Get item :id
	        //
			case $request = Util::preg_match_uri('/example/:id'): 
				return $request;
				return Database::find($request->data['id']);
			
			//
			// Delete item :id
			//			
			case $request = Util::preg_match_uri('/example/delete/:id'):
				return $request;
				return Database::delete($request->data['id']);

			//
			// Get Custom items :field/:value
			//		
			case $request = Util::preg_match_uri('/example/:field/:value'):
				return $request;
				$table = 'example52';
				$field = $request->data['field']; 
				$value = $request->data['value'];
				return Database::query("SELECT * FROM $table WHERE $field = $value;");
						
			default: return floatval(phpversion());
		}
	}
}
