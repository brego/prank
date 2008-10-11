<?php

require_once dirname(dirname(dirname(__FILE__))).DS.'core/inflector.php';

class InflectorTestCase extends PrankTestCase {
	
	public function setup() {
	}
	
	public function teardown() {
	}

	public function test_pluralize() {
		$this->assert_equal(Inflector::pluralize('ox'),      'oxen');
		$this->assert_equal(Inflector::pluralize('mouse'),   'mice');
		$this->assert_equal(Inflector::pluralize('vertex'),  'vertices');
		$this->assert_equal(Inflector::pluralize('box'),     'boxes');
		$this->assert_equal(Inflector::pluralize('query'),   'queries');
		$this->assert_equal(Inflector::pluralize('hive'),    'hives');
		$this->assert_equal(Inflector::pluralize('wife'),    'wives');
		$this->assert_equal(Inflector::pluralize('basis'),   'bases');
		$this->assert_equal(Inflector::pluralize('medium'),  'media');
		$this->assert_equal(Inflector::pluralize('person'),  'people');
		$this->assert_equal(Inflector::pluralize('child'),   'children');
		$this->assert_equal(Inflector::pluralize('tomato'),  'tomatoes');
		$this->assert_equal(Inflector::pluralize('bus'),     'buses');
		$this->assert_equal(Inflector::pluralize('alias'),   'aliases');
		$this->assert_equal(Inflector::pluralize('octopus'), 'octopuses');
		$this->assert_equal(Inflector::pluralize('crisis'),  'crises');
		$this->assert_equal(Inflector::pluralize('curses'),  'curses');
	}

	public function test_singularize() {
	}

	public function test_camelcase() {
		$this->assert_equal(Inflector::camelcase('something new'), 'SomethingNew');
		$this->assert_equal(Inflector::camelcase('something_new'), 'SomethingNew');
		$this->assert_equal(Inflector::camelcase('sOmEtHiNg nEw'), 'SomethingNew');
	}
	
	public function test_camelback() {
		$this->assert_equal(Inflector::camelback('something new'), 'somethingNew');
		$this->assert_equal(Inflector::camelback('something_new'), 'somethingNew');
		$this->assert_equal(Inflector::camelback('sOmEtHiNg nEw'), 'somethingNew');
	}

	public function test_underscore() {
		$this->assert_equal(Inflector::underscore('something new'), 'something_new');
		$this->assert_equal(Inflector::underscore('SomethingNew'),  'something_new');
		$this->assert_equal(Inflector::underscore('sOmEtHiNg nEw'), 'something_new');
	}

	public function test_humanize() {
		$this->assert_equal(Inflector::humanize('something_new'), 'Something new');
		$this->assert_equal(Inflector::humanize('SomethingNew'),  'Something new');
		$this->assert_equal(Inflector::humanize('sOmEtHiNg nEw'), 'Something new');
	}

	public function test_modelize() {
		$this->assert_equal(Inflector::modelize('models'), 'Model');
	}

	public function test_tabelize() {
		$this->assert_equal(Inflector::tabelize('Model'), 'models');
		$this->assert_equal(Inflector::tabelize('Models'), 'models');
	}

	public function test_controlize() {
		$this->assert_equal(Inflector::controlize('default'), 'DefaultController');
		$this->assert_equal(Inflector::controlize('Models'), 'ModelController');
	}
}

?>