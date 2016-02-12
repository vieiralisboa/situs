<?php

/**
 * RESTful Controller
 */
class Eurolotto_Controller {

	/**
	 * SELECT
	 */
	public function get(){		

		Router::route('/eurolotto', function($request){
			$style = "ul, ol{ list-style:none; display: inline-block; padding: 0; margin: 0; }";
			$style .= "\nbody{ background-color: rgb(0,0,0,0.05); }";
			$style .= "\nimg.button{ cursor: pointer; margin: 6px; margin-left: 18px; width: 56px; opacity: 0.75; }";//color: purple; font-family: Consolas, monospace; padding: 6px 10px; background-color: orange; border: none; cursor: pointer; opacity: 0.75; }";
			$style .= "\nimg.button:hover{ opacity: 1; }";
			$style .= "\nli{ box-shadow: 0 0 10px rgba(0,0,0,.2); background-color: rgba(255,255,255,0.25); border-radius: 28px; float: left; list-style-type:none; margin: 3px; padding: 0px; text-align: center; display: block; font-family: Consolas, monospace;}";
			$style .= "\nli a{ line-height: 56px; height: 56px; width: 56px; display: block; text-decoration: none; }";
			$style .= "\n#numbers li a{ color: rgba(0,0,0,0.8); }";
			$style .= "\n#stars li a{ color: rgba(0,0,0,0.8); }";
			$style .= "\n#numbers li{ background-color: rgba(225,255,255,0.9); }";
			$style .= "\n#stars li{ background-color: rgba(255,255,155,0.9); }";
			$style .= "\n#key{ padding: 20px; background-color: rgba(0,0,0,0.5); font-size: 40px; font-weight: bold;}";

			$warn = "I swear that I will donate at least 1/10 of my winnings, if I win with this lotto key, to the PayPal account <b>vieira.lisboa@yahoo.com</b>";
			$pre = "<pre style=\"color: rgba(0,0,0,1); padding: 10px 20px; background-color: orange; opacity: 0.8; font-size: 125%; display: inline-block;\" >$warn</pre>";

			$key = gen2($request);
			$numbers = "";
			foreach($key['numbers'] as $number){
				$numbers .= "<li title=\"Number $number\"><a href=\"#$number\">$number</a></li>\n";
			}
			$stars = "";
			foreach($key['stars'] as $star){
				$stars .= "<li title=\"Star $star\"><a href=\"#$star\">$star</a></li>\n";
			}
			$numbers = "<ul id=\"numbers\">\n$numbers</ul>";
			$stars = "<ul id=\"stars\">\n$stars</ul>";
			$button = "<img title=\"Generate Another Key\"class=\"button\" src=\"img/reload_02-56.png\" onclick=\"location.reload();\"/>";
			$lotto_key = "<ul title=\"EuroMillions Key\" id=\"key\">\n<li title=\"Numbers\">$numbers</li>\n<li title=\"Stars\">$stars</li>\n$button</ul>\n";
			
			$html = "</br><style>\n$style\n</style>\n$lotto_key";
			#$html = $pre.$html;
			$html .= "<pre>".var_export($key, true)."</pre>";

			#Router::text($html);
			Router::html($html);

			#return array($request, $_SERVER);
			#return Router::$controller_config;
		});

		Router::route('/eurolotto/late', function(){
			$late = late_numbers();
			/*
			$groups = array("numbers"=>array_keygroup($late['numbers']), "stars"=>array_keygroup($late['stars']));
			
			#$groups['numbers'] = krsort($groups['numbers']);

			$groups['stats'] = array(
				"key_count"=>array(
					"numbers"=>count($groups['numbers']),
					"stars"=>count($groups['stars'])
				)
			);
			*/
			$late['groups'] = eurolotto_latest();//$groups;
			
			return $late;
			#return late_numbers();
		});

		Router::route('/eurolotto/frequency', function($request){
			$frequency = frequency();
			$groups = array("numbers"=>array_keygroup($frequency['numbers']), "stars"=>$frequency['stars']);
			$frequency['groups'] = $groups;
			return $frequency;
			return frequency();
		});

		Router::route('/eurolotto/gen', function($request){					
			return eurolotto_key();
		});

		Router::route('/eurolotto/gen2', function($request){
			return gen2($request);
		});
	
		Router::route('/eurolotto/draws', function($request){
			$keys = keys(Database::all()); 
			$result = array();
			$count = count($keys);
			for($i=0; $i<$count; $i++){
				$j = $count - 1 - $i;
				$result[$i] = $keys[$j];
			}

			//get records from the database
			return $result;
		});

		Router::route('/eurolotto/distrib', function($request){
			$sum = eurolotto_distrib_sum($request);			
			return array(
				"sum" => $sum,
				"repeat_from_last"=>eurolotto_distrib_rep(),
				"even" => eurolotto_distrib_even()
			);
		});

		Router::route('/eurolotto/seed', function($request){
			$db = new PDO("sqlite:/htdocs/situs/storage/euromillions.sqlite");
			$result = $db->query("SELECT * FROM eurolotto");
			$rows = array();
			if(!empty($result)) {
			    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
			        $rows[] = Util::ctype_array($row);
			    }   
			}
			return Database::seed($rows);
		});

		Router::route('/eurolotto/:id', function($request) {
            $key = Database::find($request->data['id']);
            if(!$key) return $key;

            $numbers = array($key['Num_1'], $key['Num_2'],$key['Num_3'],$key['Num_4'],$key['Num_5']);
			$stars = array($key['Star_1'], $key['Star_2']);
			return array('id'=>$key['id'],'draw'=>$key['Draw'],'year'=>$key['Year'],'numbers'=>$numbers,'stars'=>$stars );
        });
	
		Router::route('/eurolotto/inlast/:n', function($request){
			return in_last_draws($request->data['n']);
		});

		Router::route('/eurolotto/waiting/:n', function($request){
			return eurolotto_waiting_number($request->data['n']);
		});

		Router::route('/eurolotto/last/:n', function($request){
			return last_draws($request->data['n']);
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
			foreach($request->input['numbers'] as $n => $Num) $request->input['Num_'.($n+1)] = $Num;
			foreach($request->input['stars'] as $s => $Star) $request->input['Star_'.($s+1)] = $Star;
	    	$lotto = new Record($request->input);
            $lotto->save();
            return $lotto;
	    });
	}
	
	// DELETE cannot delete!
}      

function eurolotto_distrib_even()
{
	$even = array("numbers"=>array(), "stars"=> array(), "T"=>0);
	$keys = Database::all();
	foreach ($keys as $key) {
		$n = even_numbers(array($key['Num_1'],$key['Num_2'],$key['Num_3'],$key['Num_4'],$key['Num_5']));
		$s = even_numbers(array($key['Star_1'],$key['Star_2']));

		if(!isset($even['numbers'][$n])) $even['numbers'][$n] = 0;
		$even['numbers'][$n]++;
		

		if(!isset($even['stars'][$s])) $even['stars'][$s] = 0;
		$even['stars'][$s]++;

		$even['T']++;
	}

	for($i=0; $i < 6; $i++) $even['numbers'][$i] = round($even['numbers'][$i]/$even['T'],2);
	for($i=0; $i < 3; $i++) $even['stars'][$i] = round($even['stars'][$i]/$even['T'],2);

	return $even;
}
//*/
function eurolotto_distrib_rep()
{
	$res = array();
	$keys = Database::all();
	$T = $X0 = $X1 = $X2 = $X3 = 0;
	$rep0 = $rep1 = $rep2 = $rep3 = array(
		"numbers"=>array(), 
		"stars"=>array(),
		"T"=>0
	);
	
	for($k=3; $k < count($keys); $k++) {
		$T++;
		$key0 = $keys[$k-3];
		$key1 = $keys[$k-2];
		$key2 = $keys[$k-1];
		$key3 = $keys[$k];

		$compare0 = eurolotto_compare_keys($key0, $key1);
		$compare1 = eurolotto_compare_keys($key1, $key2);
		$compare2 = eurolotto_compare_keys($key2, $key3);

		$n0 = $compare0['even']['numbers'];
		$s0 = $compare0['even']['stars'];

		$n1 = $compare1['even']['numbers'];
		$s1 = $compare1['even']['stars'];

		$n2 = $compare2['even']['numbers'];
		$s2 = $compare2['even']['stars'];


		if(!isset($rep0['numbers'][$n2])) $rep0['numbers'][$n2] = 0;
		$rep0['numbers'][$n2]++;
		if(!isset($rep0['stars'][$s2])) $rep0['stars'][$s2] = 0;
		$rep0['stars'][$s2]++;
		$rep0['T']++;

		// last 2 keys contains n from prev
		if($n1 > 0) {
			$rep1['T']++;
			if(!isset($rep1['numbers'][$n2])) $rep1['numbers'][$n2] = 0;
			$rep1['numbers'][$n2]++;
			if(!isset($rep1['stars'][$s2])) $rep1['stars'][$s2] = 0;
			$rep1['stars'][$s2]++;

			// last 3 keys contains n from prev
			if($n0 > 0) {
				if(!isset($rep3['numbers']["N".$n2]))
					$rep3['numbers']["N".$n2] = 0;
				$rep3['numbers']["N".$n2]++;

				if(!isset($rep3['stars']["N".$s2]))
					$rep3['stars']["N".$s2] = 0;
				$rep3['stars']["N".$s2]++;

				$rep3['T']++;
			}
		}
		else {
			$rep2['T']++;
			if(!isset($rep2['numbers'][$n2])) $rep2['numbers'][$n2] = 0;
			$rep2['numbers'][$n2]++;
			if(!isset($rep2['stars'][$s2])) $rep2['stars'][$s2] = 0;
			$rep2['stars'][$s2]++;
		}
	}

	for($i=0; $i<6; $i++)
		if(isset($rep0['numbers'][$i]))
			$rep0['numbers'][$i] = round($rep0['numbers'][$i]/$rep0['T'],2);
	for($i=0; $i<3; $i++)
		if(isset($rep0['stars'][$i]))
			$rep0['stars'][$i] = round($rep0['stars'][$i]/$rep0['T'],2);

	//
	for($i=0; $i<6; $i++)
		if(isset($rep1['numbers'][$i]))
			$rep1['numbers'][$i] = round($rep1['numbers'][$i]/$rep1['T'],2);
	for($i=0; $i<3; $i++)
		if(isset($rep1['stars'][$i]))
			$rep1['stars'][$i] = round($rep1['stars'][$i]/$rep1['T'],2);

	//
	for($i=0; $i<6; $i++)
		if(isset($rep2['numbers'][$i]))
			$rep2['numbers'][$i] = round($rep2['numbers'][$i]/$rep2['T'],2);
	for($i=0; $i<3; $i++)
		if(isset($rep2['stars'][$i]))
			$rep2['stars'][$i] = round($rep2['stars'][$i]/$rep2['T'],2);


	$rep0['T'] = $T;
	$res['PREV_UNKNOWN'] = $rep0;
	$res['PREV_YES'] = $rep1;
	$res['PREV_NO'] = $rep2;
	$res['PREV_YES2'] = $rep3;

	return $res;

}
//*/

function Zeurolotto_distrib_rep($request)
{
	$rep = array("numbers"=>array("T"=>0),"stars"=>array("T"=>0));
	$keys = Database::all();
	for($k=1; $k < count($keys); $k++) {
		$prev = $keys[$k-1];
		$key = $keys[$k];

		$compared = eurolotto_compare_keys($prev, $key);

		$n = $compared['even']['numbers'];	
		if(!isset($rep['numbers'][$n])) $rep['numbers'][$n] = 0;
		$rep['numbers'][$n]++;
		$rep['numbers']['T']++;
		
		$s = $compared['even']['stars'];	
		if(!isset($rep['stars'][$s])) $rep['stars'][$s] = 0;
		$rep['stars'][$s]++;
		$rep['stars']['T']++;
	}

	for($i = 0; $i<6; $i++){
		$rep['numbers'][$i] = round($rep['numbers'][$i]/$rep['numbers']['T'],2);	
	}

	for($i = 0; $i<3; $i++){
		$rep['stars'][$i] = round($rep['stars'][$i]/$rep['stars']['T'],2);
	}

	$rep['IF_NONE'] = array("numbers"=>array("T"=>0),"stars"=>array("T"=>0));
	$rep['IF_SOME'] = array("numbers"=>array("T"=>0),"stars"=>array("T"=>0));

	// last contained from previous
	for($k=2; $k < count($keys); $k++) {
		$prev = $keys[$k-2];
		$last = $keys[$k-1]; 
		$key = $keys[$k];
		
		$compared0 = eurolotto_compare_keys($prev, $last);
		$compared1 = eurolotto_compare_keys($last, $key); 

		$n0 = $compared0['even']['numbers'];
		$n1 = $compared1['even']['numbers'];
		if($n0 > 0) {
			if(!isset($rep['IF_SOME']['numbers'][$n1])) $rep['IF_SOME']['numbers'][$n1] = 0;
			$rep['IF_SOME']['numbers'][$n1]++;
			$rep['IF_SOME']['numbers']['T']++;
		}
		else {
			if(!isset($rep['IF_NONE']['numbers'][$n1])) $rep['IF_NONE']['numbers'][$n1] = 0;
			$rep['IF_NONE']['numbers'][$n1]++;
			$rep['IF_NONE']['numbers']['T']++;
		}
		
		$s0 = $compared0['even']['stars'];
		$s1 = $compared1['even']['stars'];
		if($s0 > 0) {
			if(!isset($rep['IF_SOME']['stars'][$s1])) $rep['IF_SOME']['stars'][$s1] = 0;
			$rep['IF_SOME']['stars'][$s1]++;
			$rep['IF_SOME']['stars']['T']++;
		}
		else {
			if(!isset($rep['IF_NONE']['stars'][$s1])) $rep['IF_NONE']['stars'][$s1] = 0;
			$rep['IF_NONE']['stars'][$s1]++;
			$rep['IF_NONE']['stars']['T']++;
		}
	}

	for($i = 0; $i<6; $i++){
		$rep['IF_NONE']['numbers'][$i] = round($rep['IF_NONE']['numbers'][$i]/$rep['IF_NONE']['numbers']['T'],2);
		$rep['IF_SOME']['numbers'][$i] = round($rep['IF_SOME']['numbers'][$i]/$rep['IF_SOME']['numbers']['T'],2);	
	}
	for($i = 0; $i<3; $i++){
		$rep['IF_NONE']['stars'][$i] = round($rep['IF_NONE']['stars'][$i]/$rep['IF_NONE']['stars']['T'],2);
		$rep['IF_SOME']['stars'][$i] = round($rep['IF_SOME']['stars'][$i]/$rep['IF_SOME']['stars']['T'],2);
	}
	
	return $rep;
}


function eurolotto_key_array($key)
{
	return array(
		"numbers"=>array($key['Num_1'],$key['Num_2'],$key['Num_3'],$key['Num_4'],$key['Num_5']),
		"stars"=>array($key['Star_1'],$key['Star_2'])
	);
}


function eurolotto_compare_keys($key1, $key2){
	$res = array();
	
	$keys = array(eurolotto_key_array($key1), eurolotto_key_array($key2));
	
	$res['even'] = array(
		"numbers"=>compare($keys[0]['numbers'],$keys[1]['numbers']),
		"stars"=>compare($keys[0]['stars'],$keys[1]['stars'])
	);

	return $res;
}


function eurolotto_distrib_sum($request)
{
	$result_n = array("T" => 0, "[100,159]"=>array("T"=>0), "[125,134]"=>array("T"=>0));
	$result_s = array("T" => 0);
	
	foreach (Database::all() as $key) {
		$t = $key['Num_1']+$key['Num_2']+$key['Num_3']+$key['Num_4']+$key['Num_5'];
		$T = round($t/10)*10;				
		if(!isset($result_n[$T])) $result_n[$T] = 1;
		else $result_n[$T]++;
		$result_n["T"]++;

		$s = $key['Star_1'] + $key['Star_2'];
		$S = round($s/2)*2;
		if(!isset($result_s[$S])) $result_s[$S] = 1;
		else $result_s[$S]++;
		$result_s["T"]++;

		if($t > 124 && $t < 135) $result_n["[125,134]"]["T"]++;
		if($t> 99 && $t < 160) $result_n["[100,159]"]["T"]++;

		if($s > 7 && $s < 19) $result_s["[8,18]"]["T"]++;
		if($s > 11 && $s < 15) $result_s["[12,14]"]["T"]++;
	}

	$result_n["[100,159]"]["%"] = round(1000*$result_n["[100,159]"]["T"]/$result_n["T"])/10;
	$result_n["[125,134]"]["%"] = round(1000*$result_n["[125,134]"]["T"]/$result_n["T"])/10;

	$result_s["[8,18]"]["%"] = round(1000*$result_s["[8,18]"]["T"]/$result_s["T"])/10;
	$result_s["[12,14]"]["%"] = round(1000*$result_s["[12,14]"]["T"]/$result_s["T"])/10;

	return array("numbers"=>$result_n, "stars"=>$result_s);
}

function keys($draws){
	// FROM DATABASE
	// put the numbers and the stars in arrays before returning
	$result = array();
	foreach($draws as $key){				
		$numbers = array($key['Num_1'], $key['Num_2'],$key['Num_3'],$key['Num_4'],$key['Num_5']);
		$stars = array($key['Star_1'], $key['Star_2']);
		$result[] = array('id'=>$key['id'],'draw'=>$key['Draw'],'year'=>$key['Year'],'numbers'=>$numbers,'stars'=>$stars );
	}
	return $result;
}

function eurolotto_key(){
	$key = array("numbers" => keygen(5, 1, 50), "stars" => keygen(2, 1, 11));
	return array( 
		"key"=> $key,
		"stats"=>eurolotto_stats($key)//"even"=>array("numbers"=>even_numbers($numbers['numbers']),"stars"=>even_numbers($stars['numbers']))
	);
}

function keygen($n, $min, $max, $numbers = array()){
	while(count($numbers)<$n){
		$rand = round(rand($min, $max));
		// verificar se o número já conta
		foreach($numbers as $number) 
			if($number == $rand) break;
		if($number == $rand) continue;
		$numbers[] = $rand;
	}
	sort($numbers);
	return $numbers;
}

function last_draws($n){
	$records = Database::all();
	$c = count($records);
	$result = array();				
	for($i = $c-$n; $i<$c; $i++){
		$key = $records[$i]; 
		$numbers = array($key['Num_1'], $key['Num_2'],$key['Num_3'],$key['Num_4'],$key['Num_5']);
		$stars = array($key['Star_1'], $key['Star_2']);
		$result[] = array('id'=>$key['id'],'draw'=>$key['Draw'],'year'=>$key['Year'],'numbers'=>$numbers,'stars'=>$stars );
	}
	return $result;
}

/**
 * 
 */
function ild_helper($index, $draws){
	$numbers = array();
	foreach ($draws as $draw) {
		foreach($draw[$index] as $number)
			if(!isset($numbers[$number]))
				$numbers[$number] = 1;
			else $numbers[$number]++; 
	}
	$ns = array();
	foreach($numbers as $n => $N) {
		if(!isset($ns[$N])) $ns[$N] = array();
		$ns[$N][] = $n;
	}
	return $ns;
}

/**
 * Merges an array of associative arrays into an associative array
 * [{ n:[1,2], s:[3,0] }, {n:[10], s:[13,6,1] }] => { n:[1,2,10], s:[3,0,6,1] } 
 */
function merge_arrays($arrs) {
	$res = array();
	$numbers = array();
	foreach ($arrs as $arr) {
		foreach ($arr as $key => $val) {
			if(!isset($res[$key])) $res[$key] = array(); 
			if(!isset($numbers[$key])) $numbers[$key];
			if(is_array($val)) {
				foreach ($val as $value) {
					if(!isset($numbers[$key][$value])) {
						$numbers[$key][$value] = 1;
						$res[$key][] = $value;
					}
					else $numbers[$key][$value]++;
				}
			}
			else {
				if(!isset($numbers[$key][$val])) {
					$numbers[$key][$val] = 1;
					$res[$key][] = $val;
				}
				else $numbers[$key][$val]++;
			}
			sort($res[$key]);
		}
	}
	return $res;
}

/**
 * Returns an array with the numbers from the last n draws  
 */
function in_last_draws($n){
	// last n draws
	$draws = last_draws($n);
	#return merge_arrays($draws);

	$res = array();
	$res['numbers'] = ild_helper("numbers", $draws);
	$res['stars'] = ild_helper("stars", $draws);

	return $res;
}

function aksort(&$array,$valrev=false,$keyrev=false) {
  if ($valrev) { arsort($array); } else { asort($array); }
    $vals = array_count_values($array);
    $i = 0;
    foreach ($vals AS $val=>$num) {
        $first = array_splice($array,0,$i);
        $tmp = array_splice($array,0,$num);
        if ($keyrev) { krsort($tmp); } else { ksort($tmp); }
        $array = array_merge($first,$tmp,$array);
        unset($tmp);
        $i = $num;
    }
}

// counts even numbers
function even_numbers($numbers){
	$even = 0;
	foreach ($numbers as $number) {
		if($number%2 == 0) $even++;
	}
	return $even;
}

/**
 * Compares numbers from 2 arrays
 * Returns the number of elements found in both arrays
 */
function compare($arr1, $arr2){
	$k=0;
	for($i=0; $i<count($arr1); $i++){
		for ($j=0; $j < count($arr2); $j++) { 
			if($arr1[$i] == $arr2[$j]) $k++;
		}
	}
	return $k;
}

/**
 * 
 */
function late_numbers(){
	$keys = keys(Database::all());
	$numbers = array();
	for($i=1; $i<51; $i++) $numbers[$i] = 0;
	foreach($keys as $key) {
		for($i=1; $i<51; $i++) $numbers[$i]++;
		foreach($key['numbers'] as $number) $numbers[$number] = 0;
	}
	$stars = array();
	for($i=1; $i<12; $i++) $stars[$i] = 0;
	foreach($keys as $key) {
		for($i=1; $i<12; $i++) $stars[$i]++;
		foreach($key['stars'] as $star) $stars[$star] = 0;
	}
	$lates = array();
	foreach($numbers as $number => $late){
		if(!isset($lates[$late])) $lates[$late] = array();
		$lates[$late][] = $number;
	}
	ksort($lates);
	return array("numbers"=>$numbers, "stars"=>$stars);
}

function eurolotto_latest_numbers($n){
	$late = late_numbers();
	$groups = array_keygroup($late['numbers']);
	$keys = array();
	foreach($groups as $key=>$val) {
		$keys[] = $key;
	}
	rsort($keys);	
	$res = array();
	$k = 0;
	foreach($keys as $key){
		foreach($groups[$key] as $number){
			$res[] = $number;
			$k++;
		}
		if($k >= $n) break;
	}
	return $res;
}

function eurolotto_latest($n=0){
	$late = late_numbers();
	$res = array(
		"numbers"=>array_keygroup($late['numbers']),
		"stars"=>array_keygroup($late['stars'])
	);

	$res['stats'] = array(
		"key_count"=>array(
			"numbers"=>count($res['numbers']),
			"stars"=>count($res['stars'])
		),
		//"keys"=>$keys,
		"late10"=> eurolotto_latest_numbers(10)
	);

	return $res;	
}

function array_keygroup($arr){
	$res = array();
	foreach ($arr as $key => $value) {
		if(!isset($res[$value])) $res[$value] = array();
		$res[$value][] = $key;
	}
	return $res;
}

function frequency(){
	$keys = keys(Database::all());
	$res = array("numbers"=>array(),"stars"=>array());
	foreach($keys as $key){
		foreach ($key['numbers'] as $n){
			if(!isset($res['numbers'][$n])) $res['numbers'][$n] = 0;
			$res['numbers'][$n]++;
		}
		foreach ($key['stars'] as $s){
			if(!isset($res['stars'][$s])) $res['stars'][$s] = 0;
			$res['stars'][$s]++;
		}
	}
	return $res;
}

function from_array($indexes, $array){
	$res = array();
	foreach($indexes as $index){
		$res[$index] = $array[$index];
	}
	return $res; 	
}

function eurolotto_waiting($key){
	$waiting = late_numbers();
	return array(
		"numbers" => from_array($key['numbers'], $waiting['numbers']),
		"stars" => from_array($key['stars'], $waiting['stars'])
	); 
}

function eurolotto_waiting_number($number){
	return _eurolotto_waiting($number, "numbers");
}

function eurolotto_waiting_star($star){
	return _eurolotto_waiting($star, "stars");
}

function _eurolotto_waiting($number, $index){
	$delays = late_numbers();
	foreach($delays[$index] as $n => $delay){
		if($number == $n) return $delay;
	}
}

function eurolotto_done($key){
	$done = frequency();
	return array(
		"numbers"=>from_array($key['numbers'],$done['numbers']),
		"stars"=>from_array($key['stars'],$done['stars'])
	);
}

//
function eurolotto_stats($key){
	$res = array();

	// sum
	$res['SUM'] =  array(
		"numbers" => array_sum($key['numbers']),
		"stars" => array_sum($key['stars'])		
	); 

	// even
	$res['EVEN'] = array(
		"numbers" => even_numbers($key['numbers']),
		"stars" => even_numbers($key['stars'])
	);

	// late
	$res['WAIT'] = eurolotto_waiting($key);

	// frequency
	$res['FREQ'] = eurolotto_done($key);

	return $res;
}

/*
class Eurolotto {
	public $keys;
	public function keys(){

	}

	function __construct($data){
		$keys = self::keys(Database::all());
	}
}
*/

// generate special key
function gen2($request){
	$done = false;
	$last2 = last_draws(2);
	$last_draw = in_last_draws(1);
	$tild10 = in_last_draws(10);

	$late_10 = eurolotto_latest_numbers(10);
	//$num = $late_10[rand(0,9)];

	// numbers to include (to start with)
	$include = array();
	if(eurolotto_waiting_number(50) > 15) $include[] = 50;
	$include[] = $late_10[rand(0,9)];


	// NUMBERS [1xTild + 1xLate]
	while(!$done) {
		$numbers = keygen(5, 1, 50, $include);

		/* 1 */
		// RANGE [100,160]
		$sum = array_sum($numbers);
		if($sum < 100 || $sum > 160) continue;

		/* 2 */
		// AT LEAST 1 NUMBER FROM (TWICE IN) LAST 10 DRAWS
		$n = compare($numbers, $tild10['numbers'][2]);
		if($n != 2) continue;

		/* 3 */
		// 1 NUMBER FROM LAST DRAW
		// if last draw does not contain N from last
		$l = compare($last2[0]['numbers'], $last2[1]['numbers']);
		if($l == 0) {		
			$k = compare($numbers, $last_draw['numbers'][1]);
			if($k == 0) continue;
		}


		/* 4 */
		// 3 EVEN NUMBERS 
		// if last draw contained less than 3 even numbers
		$p = even_numbers($last_draw['numbers'][1]);
		$even_n = even_numbers($numbers);
		// if previous key has less than 3 even numbers
		if($p < 3) {
			// set has 3 even numbers (or get a new set on numbers)
			if($even_n != 3) continue;
		}
		else// set has 2 even numbers (or get a new set on numbers)  
			if($even_n != 2) continue;
		$res = array("numbers" => $numbers);

		/*				
		$stats_n = array(
			"last_draw" => array(
				"key" => $last_draw,
				"even_numbers" => $p,
				"from_previous_draw" => $l,	
			),
			"this_key" => array(
				"even_numbers" => $even_n,
				"from_last_draw" => $k,
				"from_2x_in_last_10_draws" => $tild_n,
				"T" => $key_n['T']				
			)
		);
		*/
		$done = true;
	}
	$done = false;

	// STARS
	while(!$done) {
		$stars = keygen(2, 1, 11);

		/* 3 */
		// 1 STAR FROM LAST DRAW
		// if last draw does not contain S from last
		$l = compare($last2[0]['stars'], $last2[1]['stars']);
		if($l == 0) {		
			$k = compare($stars, $last_draw['stars'][1]);
			if($k == 0) continue;					
		}
		$res['stars'] = $stars;
		$done = true;
	}

	$records = Database::all();
	$c = count($records);

	$res['LAST_DRAW'] = $records[--$c];
	$res['STATS'] = eurolotto_stats($res);
	
	return $res;	
}

// 
