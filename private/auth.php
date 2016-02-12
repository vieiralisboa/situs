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
class Auth extends Util
{

    // dummy db
    public static function db($user, $realm)
    {
        $file = dirname(__FILE__)."/auth/$realm.json";
        if (file_exists($file)) {
            $db = json_decode(file_get_contents($file));
            foreach($db as $record) {
                if($record->user == $user) return $record->pw;
            }
        }
        //else die($file);

        return false;
    }

    /**
     *
     */
    public static function user()
    {
        // no username
        if (empty($_SERVER['PHP_AUTH_USER'])) return null;

        return $_SERVER['PHP_AUTH_USER'];
    }

    /**
     *
     */
    public static function pw()
    {
        // no password
        if (empty($_SERVER['PHP_AUTH_PW'])) return null;

        return $_SERVER['PHP_AUTH_PW'];
    }

    /**
     *
     */
    public static function unauthorize($realm)
    {
        header("WWW-Authenticate: Basic realm=\"$realm\"");
        self::quit(401);
    }

    /**
     * Basic Authorization
     * Realm is Api name http://host/<Api>/:some/:more
     */
    public static function basic($realm)
    {
        // list of controllers that do not require authorization
        #$ignore = dirname(__FILE__)."\auth\.ignore.json";
        $ignore = dirname(__FILE__)."/auth/.ignore.json";
        if (file_exists($ignore)) {
            $controllers = json_decode(file_get_contents($ignore));
            foreach($controllers as $controller) {
                if($controller == $realm) {
                    return;
                }
            }
        }

        // no username or password
        if (empty($_SERVER['PHP_AUTH_USER']) || empty($_SERVER['PHP_AUTH_PW'])) {
            self::unauthorize($realm);
        }      

        //-------------------------------------------------------------------------------------------------------------------
        // Workaround for missing Authorization header under CGI/FastCGI Apache:
        //             SetEnvIf Authorization .+ HTTP_AUTHORIZATION=$0
        //
        // Now PHP should automatically declare $_SERVER[PHP_AUTH_*] variables if the client sends the Authorization header.
        //-------------------------------------------------------------------------------------------------------------------

        // get user password from the database
        //"SELECT pw FROM $realm WHERE user='{$_SERVER['PHP_AUTH_USER']}'";

        $pw = self::db(strtolower($_SERVER['PHP_AUTH_USER']), $realm);

        $auth = Util::zehash_verify($_SERVER['PHP_AUTH_PW'], $pw);


        // password verification
        if ($auth['verifies'] != true) {
            self::unauthorize($realm);
        }
    }
}
