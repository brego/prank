<?php

require_once dirname(dirname(dirname(dirname(__FILE__)))).DS.'core/helpers/base.php';

class HelpersBaseTestCase extends PrankTestCase {
	public function test_url() {
		$this->assert_equal(url('/hello/'), '/hello/');
	
		//TEST ME MORE!
	
	}
}

?>