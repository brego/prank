<?php

require_once dirname(dirname(dirname(dirname(__FILE__)))).DS.'core/model/connection.php';

if (!class_exists('User')) {
	class User extends ModelBase {
	}	
}

class ModelConnectionTestCase extends PrankTestCase {
	
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
	
	public function test_instance() {
		$this->assert_identical(ModelConnection::instance(), ModelConnection::instance());
	}
	
	public function test_query_wrapped() {
		$result = $this->db->query_wrapped('select * from users;', 'User');
		$this->assert_is_a($result, 'ModelSet');
		$this->assert_equal($result->item_name(), 'user');
	}

}

?>