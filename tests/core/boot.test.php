<?php

require_once dirname(dirname(dirname(__FILE__))).DS.'core'.DS.'boot.php';

class BootTestCase extends PrankTestCase {
	
	public function setup() {
		$this->setup_prank_spine();
	}
	
	public function teardown() {
		$this->teardown_prank_spine();
	}
	
	public function test___construct() {
		$this->assert_equal(c('ds'),          DS);
		$this->assert_equal(c('app'),         $this->app_dir);
		$this->assert_equal(c('core'),        ROOT.'core'.DS);
		$this->assert_equal(c('models'),      $this->app_dir.'models'.DS);
		$this->assert_equal(c('views'),       $this->app_dir.'views'.DS);
		$this->assert_equal(c('controllers'), $this->app_dir.'controllers'.DS);
		$this->assert_equal(c('webroot'),     $this->app_dir.'webroot'.DS);
		$this->assert_equal(c('state'),      'test');
	}
	
	public function test_run() {
		$file = <<<EOF
<?php
\$map->connect(':controller/:action/:id', array('controller'=>'default'));
?>
EOF;
		file_put_contents($this->routes_config_file, $file);
		$instance = Boot::run($this->index_file);
		
		$this->assert_identical($instance->test('url'), '/');
		$this->assert_identical($instance->test('controller'), 'default');
		$this->assert_identical($instance->test('action'), 'index');
		$this->assert_identical($instance->test('params'), array());
		$this->assert_identical($instance->test('route'), array('controller'=>'default'));
	}
}

?>