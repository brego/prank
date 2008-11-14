<?php

// require_once dirname(dirname(dirname(__FILE__))).DS.'core/inflector.php';

class InflectorTestCase extends PrankTestCase {
	
	public function setup() {
	}
	
	public function teardown() {
	}

	public function test_pluralize() {
		$this->assert_equal(pluralize('ox'),      'oxen');
		$this->assert_equal(pluralize('mouse'),   'mice');
		$this->assert_equal(pluralize('vertex'),  'vertices');
		$this->assert_equal(pluralize('box'),     'boxes');
		$this->assert_equal(pluralize('query'),   'queries');
		$this->assert_equal(pluralize('hive'),    'hives');
		$this->assert_equal(pluralize('wife'),    'wives');
		$this->assert_equal(pluralize('basis'),   'bases');
		$this->assert_equal(pluralize('medium'),  'media');
		$this->assert_equal(pluralize('person'),  'people');
		$this->assert_equal(pluralize('child'),   'children');
		$this->assert_equal(pluralize('tomato'),  'tomatoes');
		$this->assert_equal(pluralize('bus'),     'buses');
		$this->assert_equal(pluralize('alias'),   'aliases');
		$this->assert_equal(pluralize('octopus'), 'octopuses');
		$this->assert_equal(pluralize('crisis'),  'crises');
		$this->assert_equal(pluralize('curses'),  'curses');
	}

	public function test_singularize() {
	}

	public function test_camelcase() {
		$this->assert_equal(camelcase('something new'), 'SomethingNew');
		$this->assert_equal(camelcase('something_new'), 'SomethingNew');
		$this->assert_equal(camelcase('sOmEtHiNg nEw'), 'SomethingNew');
	}
	
	public function test_camelback() {
		$this->assert_equal(camelback('something new'), 'somethingNew');
		$this->assert_equal(camelback('something_new'), 'somethingNew');
		$this->assert_equal(camelback('sOmEtHiNg nEw'), 'somethingNew');
	}

	public function test_underscore() {
		$this->assert_equal(underscore('something new'), 'something_new');
		$this->assert_equal(underscore('SomethingNew'),  'something_new');
		$this->assert_equal(underscore('sOmEtHiNg nEw'), 'something_new');
	}

	public function test_human() {
		$this->assert_equal(human('something_new'), 'Something new');
		$this->assert_equal(human('SomethingNew'),  'Something new');
		$this->assert_equal(human('sOmEtHiNg nEw'), 'Something new');
	}

	public function test_to_model() {
		$this->assert_equal(to_model('models'), 'Model');
	}

	public function test_to_table() {
		$this->assert_equal(to_table('Model'), 'models');
		$this->assert_equal(to_table('Models'), 'models');
	}

	public function test_to_controller() {
		$this->assert_equal(to_controller('default'), 'DefaultController');
		$this->assert_equal(to_controller('Models'), 'ModelController');
	}
}

?>