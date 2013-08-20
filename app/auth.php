<?php
/**
 * Situs - A PHP Framework
 *
 * @package  Situs
 * @version  0.0.0
 * @author   José Vieira Lisboa <jose.vieira.lisboa@gmail.com>
 * @link     http://situs.pt
 */

/**
 * Authorization
 */
class Auth extends Util{
	
	// Dummy Users
	static $users = array(
		'toolbar' => array(
			array('user' => 'guest', 'pw' => 'guest'),
			array('user' => 'myip', 'pw' => 'piym'),
			array('user' => 'root', 'pw' => 'dem0n'),
			array('user' => 'daniel', 'pw' => 'dem0nio'),
			array('user' => 'anonymous', 'pw' => 'anonymous')
		),
		'toolbar52' => array(
			array('user' => 'guest', 'pw' => 'guest'),
			array('user' => 'myip', 'pw' => 'piym'),
			array('user' => 'root', 'pw' => 'dem0n'),
			array('user' => 'daniel', 'pw' => 'dem0nio'),
			array('user' => 'anonymous', 'pw' => 'anonymous')
		),
		'myTV' => array(
			array('user' => 'guest', 'pw' => 'guest'),
			array('user' => 'paula', 'pw' => 'pserafim')
		),
		'myTV52' => array(
			array('user' => 'guest', 'pw' => 'guest')
		),
		'closure_compiler' => array(
			array('user' => 'guest', 'pw' => 'guest'),
			array('user' => 'anonymous', 'pw' => 'anonymous')
		),
		'closure_compiler52' => array(
			array('user' => 'guest', 'pw' => 'guest'),
			array('user' => 'anonymous', 'pw' => 'anonymous')

		),
		'myip' =>array(
			array('user' => 'guest', 'pw' => 'guest'),
			array('user' => 'anonymous', 'pw' => 'anonymous')

		),
		'myip52' =>array(
			array('user' => 'guest', 'pw' => 'guest'),
			array('user' => 'anonymous', 'pw' => 'anonymous')

		)
	);

	// dummy db
	public static function db($user, $realm){
		if(!isset(self::$users[$realm])) return false;

		$db = self::$users[$realm];

		foreach($db as $record)
			if($record['user'] == $_SERVER['PHP_AUTH_USER'])
				return $record['pw'];
			
		return false;
	}

	/**
	 *
	 */
	public static function unauthorize($realm){
	    header("WWW-Authenticate: Basic realm=\"$realm\"");
	    self::quit(401);
	}

	/**
	 * Basic Authorization
	 * Realm is Api name http://host/<Api>/:some/:more
	 */
	public static function basic($realm = false){
		switch($realm)
		{
			// Basic Authorization not required
			case 'toolbar':
			case 'toolbar52':
			case 'download':
			case 'download52':
			case 'situs':
			case 'situs52': 
				return;

			// default realm
			default:
				if($realm === false) $realm = 'situs';
		}

		// no username or password
		if (empty($_SERVER['PHP_AUTH_USER']) || empty($_SERVER['PHP_AUTH_PW'])) 
			self::unauthorize($realm);

		// get user password from the database
		//"SELECT pw FROM $realm WHERE user='{$_SERVER['PHP_AUTH_USER']}'";
		$pw = self::db($_SERVER['PHP_AUTH_USER'], $realm);

		// password verification
		if($_SERVER['PHP_AUTH_PW'] != $pw) self::unauthorize($realm);
	}
}