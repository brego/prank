<?php
/**
 * The session interface
 *
 * @filesource
 * @copyright  Copyright (c) 2008-2014, Kamil "Brego" Dzieliński
 * @license    http://opensource.org/licenses/mit-license.php The MIT License
 * @author     Kamil "Brego" Dzieliński <brego@brego.dk>
 * @link       http://prank.brego.dk/ Prank's project page
 * @link       http://github.com/brego/prank/ Prank's Git repository
 * @package    Prank
 * @subpackage Core
 * @since      Prank 0.10
 * @version    Prank 0.75
 */

/**
 * Basic interface to session handling
 *
 * @package    Prank
 * @subpackage Core
 */
class Session {
	public static $instance = null;

	private function __construct() {
		session_start();
	}

	public function __set($name, $value) {
		$_SESSION[$name] = $value;
		return true;
	}

	public function __get($name) {
		if (isset($_SESSION[$name])) {
			return $_SESSION[$name];
		} else {
			return null;
		}
	}

	public function __isset($name) {
		return isset($_SESSION[$name]);
	}

	public static function instance() {
		if (self::$instance === null) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public static function destroy() {
		session_start();
		$_SESSION = [];
		$params   = session_get_cookie_params();
	    setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
		session_regenerate_id();
		session_destroy();
	}
}

?>