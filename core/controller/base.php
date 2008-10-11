<?php
/**
 * Baseclass for all controllers.
 *
 * All controllers extend this base class. Contains methods for rendering,
 * callbacks, and communication with the view.
 *
 * PHP version 5.3.
 *
 * @filesource
 * @copyright  Copyright (c) 2008, Kamil "Brego" Dzieliński
 * @license    http://opensource.org/licenses/mit-license.php The MIT License
 * @author     Kamil "Brego" Dzieliński <brego@brego.dk>
 * @link       http://prank.brego.dk Prank's project page
 * @package    Prank
 * @subpackage Core.Controller
 * @since      Prank 0.10
 * @version    Prank 0.10
 */

namespace Prank::Controller;

class Base {
	public $action         = null;
	public $view           = null;
	public $params         = array();
	public $layout         = 'default';
	public $shortname      = null;
	public $view_variables = array();
	
	public function __set($var, $val) {
		$this->view_variables[$var] = $val;
	}
	
	public function run() {
		$this->before_run();
		$this->before_render();
			
		ob_start();
		
		call_user_func_array(array($this, $this->action), $this->params);
			
		$this->after_run();
		
		extract($this->view_variables);

		if (file_exists(::c('VIEWS').$this->shortname.::c('DS').$this->view.'.php') && $this->view !== false) {
			require_once ::c('VIEWS').$this->shortname.::c('DS').$this->view.'.php';
		}
		
		$content_for_layout = ob_get_clean();
			
		if (file_exists(::c('VIEWS').'layouts'.::c('DS').$this->layout.'.php') && $this->layout !== false) {
			ob_start();
			require_once ::c('VIEWS').'layouts'.::c('DS').$this->layout.'.php';
			$output = ob_get_clean();
		} else {
			$output = $content_for_layout;
		}
		
		$this->after_render($output);
	}
	
	public function before_run() {
	}
	
	public function after_run() {
	}
	
	public function before_render() {
	}
	
	public function after_render($content = null) {
		print $content;
	}
}

?>