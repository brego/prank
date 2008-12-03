<?php

require_once dirname(dirname(dirname(dirname(__FILE__)))).DS.'core/helpers/html.php';

class HelpersHtmlTestCase extends PrankTestCase {
	
	public function setup() {
	}
	
	public function teardown() {
	}
	
	public function test_is_current_page() {
		$controller = Registry::instance()->current_controller;
		
		$test = is_current_page(array('controller'=>$controller->controller, 'action'=>$controller->action));
		$this->assert_true($test);
		
		$test = is_current_page(array('controller'=>$controller->controller));
		$this->assert_true($test);
		
		$test = is_current_page(array('action'=>$controller->action));
		$this->assert_true($test);
		
		$test = is_current_page('/'.$controller->controller.'/'.$controller->action);
		$this->assert_true($test);
		
		$test = is_current_page('/'.$controller->controller);
		$this->assert_true($test);
		
		$test = is_current_page($controller->action);
		$this->assert_true($test);
	}
	
	public function test_link_to() {
		$controller = Registry::instance()->current_controller->controller;
		
		$test     = link_to('http://brego.dk/', 'Brego', array('id'=>'brego', 'title'=>'Title'));
		$expected = '<a href="http://brego.dk/" id="brego" title="Title">Brego</a>';
		$this->assert_equal($test, $expected);
		
		$test     = link_to('/controller/action', 'action');
		$expected = '<a href="/controller/action">action</a>';
		$this->assert_equal($test, $expected);
		
		$test     = link_to('action');
		$expected = '<a href="/'.$controller.'/action">/'.$controller.'/action</a>';
		$this->assert_equal($test, $expected);
		
		$test     = link_to('/controller/action', array('id'=>'action'));
		$expected = '<a href="/controller/action" id="action">/controller/action</a>';
		$this->assert_equal($test, $expected);
		
		$test     = link_to(array('action'=>'my_action'), 'My Action', array('id'=>'action'));
		$expected = '<a href="/'.$controller.'/my_action" id="action">My Action</a>';
		$this->assert_equal($test, $expected);
		
		$test     = link_to(array('action'=>'my_action'));
		$expected = '<a href="/'.$controller.'/my_action">/'.$controller.'/my_action</a>';
		$this->assert_equal($test, $expected);
	}
	
	public function test_link_to_unless_current() {
		$test     = link_to_unless_current('index', 'Home');
		$expected = 'Home';
		$this->assert_equal($test, $expected);
		
		$test     = link_to_unless_current('index', 'Home', function($name) {
			return link_to('index', $name, array('class'=>'current'));
		});
		$expected = '<a href="/default/index" class="current">Home</a>';
		$this->assert_equal($test, $expected);
	}

	public function test_image_path() {
		$test     = image_path('my.jpg');
		$expected = '/images/my.jpg';
		$this->assert_equal($test, $expected);
		
		$test     = image_path('catalog/my.jpg');
		$expected = '/images/catalog/my.jpg';
		$this->assert_equal($test, $expected);
		
		$test     = image_path('/my_catalog/my.jpg');
		$expected = '/my_catalog/my.jpg';
		$this->assert_equal($test, $expected);
		
		$test     = image_path('http://example.com/my.jpg');
		$expected = 'http://example.com/my.jpg';
		$this->assert_equal($test, $expected);
	}

	public function test_image() {
		$test     = image('http://example.com/my.jpg', array('alt'=>'Alternative'));
		$expected = '<img src="http://example.com/my.jpg" alt="Alternative" />';
		$this->assert_equal($test, $expected);
		
		$test     = image('my.jpg');
		$expected = '<img src="/images/my.jpg" />';
		$this->assert_equal($test, $expected);
	}
	
	public function test_stylesheet_path() {
		$test     = stylesheet_path('my');
		$expected = '/stylesheets/my.css';
		$this->assert_equal($test, $expected);
		
		$test     = stylesheet_path('catalog/my');
		$expected = '/stylesheets/catalog/my.css';
		$this->assert_equal($test, $expected);
		
		$test     = stylesheet_path('/my_catalog/my');
		$expected = '/my_catalog/my.css';
		$this->assert_equal($test, $expected);
		
		$test     = stylesheet_path('http://example.com/my');
		$expected = 'http://example.com/my.css';
		$this->assert_equal($test, $expected);
	}
	
	public function test_stylesheet() {
		$test     = stylesheet('http://example.com/my', array('media'=>'screen'));
		$expected = '<link href="http://example.com/my.css" rel="stylesheet" type="text/css" media="screen" />';
		$this->assert_equal($test, $expected);

		$test     = stylesheet('my');
		$expected = '<link href="/stylesheets/my.css" rel="stylesheet" type="text/css" />';
		$this->assert_equal($test, $expected);
	}
	
	public function test_html_parse_params() {
		$test     = html_parse_params(null);
		$expected = null;
		$this->assert_equal($test, $expected);
		
		$test     = html_parse_params(array('one'=>'a', 'two'=>'b'));
		$expected = ' one="a" two="b"';
		$this->assert_equal($test, $expected);		
	}

}

?>