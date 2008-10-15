<?php

require_once dirname(dirname(dirname(dirname(__FILE__)))).DS.'core/object.php';
require_once dirname(dirname(dirname(dirname(__FILE__)))).DS.'core/model/base.php';
require_once '_user.model.php';
require_once '_car.model.php';
require_once '_profile.model.php';

class ModelBaseTestCase extends PrankTestCase {
	public $db = null;
	
	public function setup() {
		$this->setup_prank_spine();
		$this->db = ModelConnection::instance();
		require '_users.table.php';
		require '_cars.table.php';
		require '_profiles.table.php';
	}
	
	public function teardown() {
		$this->teardown_prank_spine();
		$this->db->exec('DROP TABLE `users`;');
		$this->db->exec('DROP TABLE `cars`;');
		$this->db->exec('DROP TABLE `profiles`;');
	}	
	
	public function test___construct() {
		$table = Inflector::tabelize('User');
		$this->assert_equal($table, 'users');
		
		$columns = array('id', 'email', 'password', 'name', 'admin', 'created_at');
		$columns = $this->db->columns($table);
		$this->assert_equal($columns, $columns);
		
		$test = new User;
		$this->assert_equal($test->fields(), $columns);
	}
	
	public function test_has_many() {
		$user = User::find_by_name('test2');
		$this->assert_is_a($user, 'User');
		$this->assert_true(isset($user->cars));
		$this->assert_is_a($user->cars, 'Collection');
		
		$this->assert_equal(count($user->cars), 5);
		foreach ($user->cars as $car) {
			$this->assert_is_a($car, 'Car');
		}
	}
	
	public function test_has_one() {
		$user = User::find_by_name('test1');
		$this->assert_is_a($user, 'User');
		$this->assert_true(isset($user->profile));
		$this->assert_is_a($user->profile, 'Profile');
		$this->assert_equal($user->profile->title, 'title1');
		
		$user = User::find_by_name('test2');
		$this->assert_equal($user->profile->title, 'title2');
	}
	
	public function test_belongs_to() {
		$car = Car::find_by_model('Ford1');
		$this->assert_is_a($car, 'Car');
		$this->assert_true(isset($car->user));
		$this->assert_is_a($car->user, 'User');
		$this->assert_equal($car->user->name, 'test1');
		
		$car = Car::find_by_model('Audi2');
		$this->assert_true(isset($car->user->name));
		$this->assert_equal($car->user->name, 'test2');
	}
	// 
	// public function test_has_and_belongs_to_many() {
	// 	
	// }
	
	// public function test_relationship_references() {
	// 	$user1 =& User::find_by_name('test1');
	// 	$user2 =& User::find_by_name('test1');
	// 	$this->assert_reference(&$user1, &$user2);
	// }
	
	public function test_perpetuum_relations() {
		$user = User::find_by_name('test1');
		$this->assert_false(isset($user->profile->user));
	}
	
	public function test_hollow() {
		$test = new User;
		$this->assert_true($test->hollow());
		$test->name = 'jack';
		$this->assert_false($test->hollow());
		
		$test = User::find_by_name('test1');
		$this->assert_false($test->hollow());
	}

	public function test_save() {
		$result = $this->db->query("select * from users where name='john doe';");
		$this->assert_equal($result->rowCount(), 0);
		
		$test = new User;
		$test->name = 'john doe';
		$id = $test->id;
		$this->assert_equal($id, '');
		$test->save();
		$time = $this->db->now();
		$this->assert_true(is_numeric($test->id));
		
		$result = $this->db->query("select * from users where name='john doe';");
		$this->assert_equal($result->rowCount(), 1);
		$result = $result->fetch();
		$this->assert_equal($result['created_at'], $time);
		
		$test = User::find_by_name('test1');
		$this->assert_is_a($test, 'User');
		
		$test->name = 'some new name';
		$id = $test->id;
		$test->save();
		$time = $this->db->now();
		
		$new_find = User::find_by_name('some new name');
		$this->assert_is_a($new_find, 'User');
		$this->assert_equal($new_find->id, $id);
		$this->assert_equal($new_find->updated_at, $time);
	}

	public function test___call() {
		$test = User::find_by_name('test1');
		$this->assert_is_a($test, 'User');
		$this->assert_false($test->hollow());
		
	}

	public function test___isset() {
		$test = new User;
		$this->assert_false(isset($test->name));
		$test->name = 'joe';
		$this->assert_true(isset($test->name));
		
		$test = User::find_by_name('test1');
		$this->assert_true(isset($test->id));
	}

	public function test___callStatic() {
		$test = User::find_all();
		$this->assert_is_a($test, 'Collection');
		$this->assert_equal(count($test), 2);
		
		$test = User::find_by_name('test1');
		$this->assert_is_a($test, 'User');
		$this->assert_equal($test->name, 'test1');
		
		$test = User::find_by_email('test1@email.com');
		$this->assert_is_a($test, 'User');
		$this->assert_equal($test->name, 'test1');
		
		$result = $this->db->query("select * from users where name='test1';");
		$this->assert_equal($result->rowCount(), 1);
		$result = $result->fetch();
		User::delete($result['id']);
		$result = $this->db->query("select * from users where name='test1';");
		$this->assert_equal($result->rowCount(), 0);
	}
}

?>