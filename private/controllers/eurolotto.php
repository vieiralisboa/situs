<?php

/**
 * RESTful Controller
 */
class Eurolotto_Controller {

	/**
	 * SELECT
	 */
	public function get(){
		/*Router::route('/eurolotto', function() { Router::redirect('http://situs.dev'); });*/
		
		Router::route('/eurolotto/:id', function($request) {
            $key = Database::find($request->data['id']);
            if(!$key) return $key;

            $numbers = array($key['Num_1'], $key['Num_2'],$key['Num_3'],$key['Num_4'],$key['Num_5']);
			$stars = array($key['Star_1'], $key['Star_2']);
			return array('id'=>$key['id'],'draw'=>$key['Draw'],'year'=>$key['Year'],'numbers'=>$numbers,'stars'=>$stars );
        });


		/**
		 *
		 */
		Router::route('/eurolotto', function(){
			//get records from the database
			$result = Database::all();
			
			// FROM DATABASE
			// put the numbers and the stars in arrays before returning
			$draws = array();
			foreach($result as $key){				
				$numbers = array($key['Num_1'], $key['Num_2'],$key['Num_3'],$key['Num_4'],$key['Num_5']);
				$stars = array($key['Star_1'], $key['Star_2']);
				$draws[] = array('id'=>$key['id'],'draw'=>$key['Draw'],'year'=>$key['Year'],'numbers'=>$numbers,'stars'=>$stars );
			}

			return $draws;
		});

		/**
		 *
		 */
		Router::route('/eurolotto/seed', function(){
			$db = new PDO("sqlite:/htdocs/pt/situs/lotto/lotto14.sqlite");
			$result = $db->query("SELECT * FROM eurolotto");
			$rows = array();
			if(!empty($result)) {
			    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
			        $rows[] = Util::ctype_array($row);
			    }   
			}
			return Database::seed($rows);
		});
	}

	/**
	 * INSERT
	 */
	public function post(){
		Router::route('/eurolotto', function($request) {
			// TO DATABASE
			// put numbers and stars from arrays in standalone variables
			foreach($request->input['numbers'] as $n => $Num) $request->input['Num_'.($n+1)] = $Num;
			foreach($request->input['stars'] as $s => $Star) $request->input['Star_'.($s+1)] = $Star;
			
	    	$lotto = new Record($request->input);
            $lotto->save();
            return $lotto;
	    });
	}
	

	/**
	 * UPDATE
	 */
	public function put(){
		Router::route('/eurolotto/:id', function($request) {
			// TO DATABASE
			//
			foreach($request->input['numbers'] as $n => $Num) $request->input['Num_'.($n+1)] = $Num;
			foreach($request->input['stars'] as $s => $Star) $request->input['Star_'.($s+1)] = $Star;
			
	    	$lotto = new Record($request->input);
            $lotto->save();
            return $lotto;
	    });
	}
	

	// DELETE cannot delete!
}      
