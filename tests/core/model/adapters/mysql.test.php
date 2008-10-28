<?php

require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))).DS.'core/model/adapter.php';
require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))).DS.'core/model/adapters/mysql.php';

class ModelAdaptersMysqlTestCase extends PrankTestCase {
	public $mysql    = null;
	public $db       = null;
	public $collumns = null;
	
	public function setup() {
		$this->setup_prank_spine();
		$this->db = ModelConnection::instance();
		
		require dirname(dirname(__FILE__)).DS.'mocks/_users.table.php';
		
		$config = Config::instance();
		require_once ::c('CONFIG').'db.php';
		$params = $config->db[::c('state')];
		
		$adapter     = 'ModelAdaptersMysql';
		$dsn         = $params['type'].':host='.$params['host'].';dbname='.$params['db'];
		$this->mysql = new $adapter($dsn, $params['user'], $params['password']);
		
		$this->collumns = array(
			'id' => array(
				'type'    => 'integer',
				'limit'   => 11,
				'null'    => false,
				'default' => null),
			'email' => array(
				'type'    => 'string',
				'limit'   => 255,
				'null'    => true,
				'default' => null),
			'password' => array(
				'type'    => 'string',
				'limit'   => 40,
				'null'    => true,
				'default' => null),
			'name' => array(
				'type'    => 'string',
				'limit'   => 255,
				'null'    => true,
				'default' => null),
			'admin' => array(
				'type'    => 'boolean',
				'limit'   => 1,
				'null'    => true,
				'default' => 0),
			'created_at' => array(
				'type'    => 'datetime',
				'limit'   => '',
				'null'    => true,
				'default' => null),
			'updated_at' => array(
				'type'    => 'datetime',
				'limit'   => '',
				'null'    => true,
				'default' => null));
	}
	
	public function teardown() {
		$this->teardown_prank_spine();
		$this->db->exec('DROP TABLE `users`;');
		$this->db       = null;
		$this->mysql    = null;
		$this->collumns = null;
	}

	public function test_is_column_of() {
		$this->assert_true($this->mysql->is_column_of('name', 'users'));
		$this->assert_false($this->mysql->is_column_of('something', 'users'));
	}

	public function test_fetch_columns() {
		$this->assert_equal($this->mysql->fetch_columns('users'), $this->collumns);
		$this->db->exec("ALTER TABLE `users` ADD `test` VARCHAR(255) NOT NULL;");
		$this->collumns['test'] = array('type'=>'string', 'limit'=>255, 'null'=>false, 'default'=>'');
		$this->assert_equal($this->mysql->fetch_columns('users'), $this->collumns);
	}

	public function test_columns() {
		$this->assert_equal($this->mysql->columns('users'), $this->collumns);
		$this->db->exec("ALTER TABLE `users` ADD `test` VARCHAR(225) NOT NULL;");
		$this->assert_equal($this->mysql->columns('users'), $this->collumns);
	}

	public function test_create() {
		$this->mysql->create('users', array('name'=>'joe'));
		$result = $this->db->query("select * from users where name='joe';");
		$this->assert_equal($result->rowCount(), 1);
	}

	public function test_update() {
		$result = $this->db->query("select * from users where name='test1';");
		$result = $result->fetch();
		$id     = $result['id'];
		$this->mysql->update('users', array('name'=>'joe'), "name='test1'");
		$result = $this->db->query("select * from users where name='joe';");
		$result = $result->fetch();
		$id_new = $result['id'];
		$this->assert_equal($id, $id_new);
	}

	public function test_delete() {
		$this->mysql->delete('users', "name='test1'");
		$result = $this->db->query("select * from users where name='test1';");
		$this->assert_equal($result->rowCount(), 0);
	}
	
	public function test_now() {
		$this->assert_equal($this->mysql->now(), date('Y-m-d H:i:s'));
	}

}

?>