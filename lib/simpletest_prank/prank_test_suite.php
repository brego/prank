<?php

require_once ROOT.'core'.DS.'base.php';

class PrankTestSuite extends TestSuite {
	public function __construct($label=false) {
		rm(ROOT.'tests'.DS.'tmp'.DS);
		mkdir(ROOT.'tests'.DS.'tmp'.DS);
		
		parent::__construct($label);
	}
}

?>