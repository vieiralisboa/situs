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
	// dummy users
	static $users = array(
		array('user' => 'guest', 'pw' => 'guest'),
		array('user' => 'myip', 'pw' => 'piym'),
		array('user' => 'root', 'pw' => 'dem0n'),
		array('user' => 'daniel', 'pw' => 'dem0nio')
	);

	// dummy db
	public static function db($user, $realm){
		foreach(self::$users as $user)
			if($user['user'] == $_SERVER['PHP_AUTH_USER'])
				return $user['pw'];
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
	 *
	 */
	public static function basic($realm = false){
		// Realm (API)
		if($realm === false) $realm = 'Situs';

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