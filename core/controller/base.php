<?php
/**
 * Baseclass for all controllers
 * 
 * @filesource
 * @copyright  Copyright (c) 2008, Kamil "Brego" Dzieliński
 * @license    http://opensource.org/licenses/mit-license.php The MIT License
 * @author     Kamil "Brego" Dzieliński <brego@brego.dk>
 * @link       http://prank.brego.dk Prank's project page
 * @package    Prank
 * @subpackage Controller
 * @since      Prank 0.10
 * @version    Prank 0.25
 */

/**
 * Baseclass for all controllers
 *
 * All controllers extend this base class. Contains methods for rendering,
 * callbacks, and communication with the view.
 *
 * @package    Prank
 * @subpackage Controller
 */
class ControllerBase {
	public  $view_variables    = array();
	public  $action            = null;
	public  $view              = null;
	public  $parameters        = array();
	public  $layout            = 'default';
	public  $controller        = null;
	private $params_calculated = false;

/**
 * All unknown variables are defined as view variables
 *
 * @param string $var
 * @param string $val 
 * @return void
 */
	public function __set($var, $val) {
		$this->view_variables[$var] = $val;
	}
	
	public function __get($property) {
		if (isset($this->view_variables[$property])) {
			return $this->view_variables[$property];
		} elseif ($property === 'params') {
			if ($this->params_calculated === true) {
				return $this->parameters;
			} else {				
				$reflection      = new ReflectionMethod($this, $this->action);
				$parameter_names = array();
				$parameters      = array();

				foreach ($reflection->getParameters() as $param) {
					$parameter_names[] = $param->getName();
				}
				$i = 0;
				foreach ($this->parameters as $value) {
					$parameters[$parameter_names[$i]] = $value;
					$i++;
				}
				$this->params_calculated = true;
				$parameters = array_merge($parameters, $_POST);
				// d($parameters);
				return $parameters;
			}
		} else {
			throw new Exception('Property '.$property.' is not defined.');
		}
	}

/**
 * Runs the controller and the view
 *
 * Calls all callbacks, the action method, and renders the output. Also
 * provides the layout functionality.
 * 
 * @return void
 */
	public function run() {
		
		$this->before_run();
		
		call_user_func_array(array($this, $this->action), $this->parameters);
			
		$this->after_run();
		$this->before_render();
		
		extract($this->view_variables);
		
		ob_start();

		if (is_file(file_path(c()->views.$this->controller, $this->view.'.php')) && $this->view !== false) {
			require file_path(c()->views.$this->controller, $this->view.'.php');
		}
		
		$content_for_layout = ob_get_clean();
		
		$content_for_layout = $this->before_layout($content_for_layout);
			
		if (is_file(file_path(c()->views.'layouts', $this->layout.'.php')) && $this->layout !== false) {
			ob_start();
			require file_path(c()->views.'layouts', $this->layout.'.php');
			$output = ob_get_clean();
		} else {
			$output = $content_for_layout;
		}
		
		$this->after_render($output);
	}

/**
 * Callback
 *
 * Runs before acition is executed, but after the controller is set-up.
 * 
 * @return void
 */	
	public function before_run() {
	}

/**
 * Callback
 *
 * Runs after the action is run, but before any actuall rendering is done.
 * 
 * @return void
 */
	public function after_run() {
	}

/**
 * Callback
 * 
 * Runs after after_run, but still before any actuall rendering.
 *
 * @return void
 */
	public function before_render() {
	}

/**
 * Callback
 * 
 * Runs after view rendering, but before it is passed to the layout. Has to
 * return contents for the view.
 *
 * @param  string $view Contents of the view rendering
 * @return string
 */
	public function before_layout($view=null) {
		return $view;
	}

/**
 * Callback
 * 
 * Runs after the view is rendered, and inserted into the layout (if
 * applicable). If any rendering is to be displayed - this callback have to do
 * it.
 *
 * @param  string $content
 * @return void
 */
	public function after_render($content=null) {
		echo $content;
	}
}

?>