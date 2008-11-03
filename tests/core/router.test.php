<?php

require_once dirname(dirname(dirname(__FILE__))).DS.'core/router.php';

class RouterTestCase extends PrankTestCase {
	
	public function setup() {
		$this->setup_prank_spine();
	}
	
	public function teardown() {
		$this->teardown_prank_spine();
	}

	public function test_run() {
		
		$file = <<<EOF
<?php
\$map->connect('juice/:action/:id', array('controller' => 'default'));
\$map->connect(':controller/:action/:id', array('controller' => 'default', 'action' => 'index'));
?>
EOF;
		file_put_contents($this->routes_config_file, $file);
		$router = new Router;
		$output = array(
			'#^/juice(?:/([^\/]+))?(?:/([^\/]+))?[\/]*(?:([\/\-A-Za-z_0-9]+))?$#' => array(
				'names'    => array(
					0 => 'action',
					1 => 'id'),
				'defaults' => array('controller' => 'default')),
			'#^(?:/([^\/]+))?(?:/([^\/]+))?(?:/([^\/]+))?[\/]*(?:([\/\-A-Za-z_0-9]+))?$#' => array(
				'names'    => array(
					0 => 'controller',
					1 => 'action',
					2 => 'id'),
				'defaults' => array(
					'controller' => 'default',
					'action'     => 'index')));
		$this->assert_identical($router->routes(), $output);
		
		$test   = $router->parse_url('/my_controller/my_action/my_id/RandomParam/second_one/');
		$result = array(
			'controller' => 'my_controller',
			'action'     => 'my_action',
			'id'         => 'my_id',
			'RandomParam',
			'second_one');
		$this->assert_identical($test, $result);
		
		$test = $router->parse_url('/my_controller/my_action/my_id');
		$this->assert_identical($test, array('controller'=>'my_controller', 'action'=>'my_action', 'id'=>'my_id'));
		
		$test = $router->parse_url('/');
		// d($test);
		$this->assert_identical($test, array('controller'=>'default', 'action'=>'index'));
		
		$test = $router->parse_url('/my_controller/');
		$this->assert_identical($test, array('controller'=>'my_controller', 'action'=>'index'));
		
		$test   = $router->parse_url('/juice/bubba/id');
		$result = array('controller'=>'default', 'action'=>'bubba', 'id'=>'id');
		$this->assert_identical($test, $result);
		$this->assert_identical(Router::current_route(), $result);
	}

}

?>