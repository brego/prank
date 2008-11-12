<?php

require_once dirname(dirname(dirname(dirname(__FILE__)))).DS.'core/helpers/javascript.php';

class HelpersJavascriptTestCase extends PrankTestCase {
	
	public function setup() {
		$this->setup_prank_spine();
		mkdir(c()->webroot);
		mkdir(c()->webroot.'js');
		mkdir(c()->webroot.'tmp');
	}
	
	public function teardown() {
		$this->teardown_prank_spine();
	}
	
	public function test_javascript_link() {
		$test     = javascript_link('test');
		$expected = '<script src="js/test.js" type="text/javascript" charset="utf-8"></script>'."\n";
		$this->assert_identical($test, $expected);
		
		$test     = javascript_link('behavior.php');
		$expected = '<script src="js/behavior.php" type="text/javascript" charset="utf-8"></script>'."\n";
		$this->assert_identical($test, $expected);
		
		$test     = javascript_link('test.js');
		$expected = '<script src="js/test.js" type="text/javascript" charset="utf-8"></script>'."\n";
		$this->assert_identical($test, $expected);
	}
	
	public function test_javascript() {
		$script_1 = 'var alpha = "some text";';
		$file_1   = c()->webroot.'js'.c()->ds.'script_1.js';
		$script_2 = 'var beta = "some more text";';
		$file_2   = c()->webroot.'js'.c()->ds.'script_2.js';
		file_put_contents($file_1, $script_1);
		file_put_contents($file_2, $script_2);

		$behavior_php = javascript_link('behavior.php');
		
		$test = javascript();
		$this->assert_identical($test, $behavior_php);
		
		$test     = javascript('script_1', 'script_2');
		$expected = javascript_link('script_1').javascript_link('script_2').$behavior_php;
		$this->assert_identical($test, $expected);
	}
	
	public function test_add_javascript_behavior() {
		add_javascript_behavior('$(\'#link\').click(alert(\'link is clicked!\'));');
		$result    = $_SESSION['prank']['javascript']['behaviors'][0];
		// $expected  = '$(document).ready(function(){'."\n";
		$expected = "$('#link').click(alert('link is clicked!'));";
		// $expected .= '});';
		$this->assert_identical($result, $expected);
	}

}

?>