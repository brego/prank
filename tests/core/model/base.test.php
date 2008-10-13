<?php

require_once dirname(dirname(dirname(dirname(__FILE__)))).DS.'core/model/base.php';

if (!class_exists('User')) {
	class User extends ModelBase {
	}	
}

class ModelBaseTestCase extends PrankTestCase {
	public $db = null;
	
	public function setup() {
		$this->setup_prank_spine();
		$this->db = ModelConnection::instance();
		$this->db->exec("CREATE TABLE `users` ( 
		`id` int(11) NOT NULL auto_increment, 
		`email` varchar(255) default NULL, 
		`password` varchar(40) default NULL, 
		`name` varchar(255) default NULL, 
		`profile` text, 
		`admin` tinyint(1) default '0', 
		`created_at` datetime default NULL,
		`updated_at` datetime default NULL,
		PRIMARY KEY (`id`) 
		) ENGINE=InnoDB");
		$this->db->exec("INSERT INTO `users` SET email='test1@email.com', password='testpassword1', name='test1', profile='test1 profile text', created_at=NOW();");
		$this->db->exec("INSERT INTO `users` SET email='test2@email.com', password='testpassword2', name='test2', profile='test2 profile text', created_at=NOW();");
	}
	
	public function teardown() {
		$this->teardown_prank_spine();
		$this->db->exec('DROP TABLE `users`;');
	}	
	
	public function test___construct() {
		$table = Inflector::tabelize('User');
		$this->assert_equal($table, 'users');
		
		$columns = array('id', 'email', 'password', 'name', 'profile', 'admin', 'created_at');
		$columns = $this->db->columns($table);
		$this->assert_equal($columns, $columns);
		
		$test = new User;
		$this->assert_equal($test->fields(), $columns);
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