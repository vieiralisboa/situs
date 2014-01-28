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

	// dummy db
	public static function db($user, $realm){
		$users = (array) json_decode(file_get_contents(dirname(__FILE__).'\auth.json'));
		if(!isset($users[$realm])) return false;
		$db = $users[$realm];
		foreach($db as $record)
			if($record->user == $_SERVER['PHP_AUTH_USER'])
				return $record->pw;

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
