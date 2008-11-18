<?php

require_once dirname(dirname(dirname(dirname(__FILE__)))).DS.'core/helpers/json.php';

class HelpersJsonTestCase extends PrankTestCase {

	public function test_to_json() {
		$this->assert_equal(to_json(array('a'=>'b')), '{"a":"b"}');
	}
	
	public function test_from_json() {
		$this->assert_equal(from_json('{"a":"b"}'), array('a'=>'b'));
	}
}
?>