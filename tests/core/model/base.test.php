<?php

require_once dirname(dirname(dirname(dirname(__FILE__)))).DS.'core/object.php';
require_once dirname(dirname(dirname(dirname(__FILE__)))).DS.'core/model/base.php';
require_once 'mocks/_user.model.php';
require_once 'mocks/_car.model.php';
require_once 'mocks/_profile.model.php';
require_once 'mocks/_author.model.php';
require_once 'mocks/_article.model.php';
require_once 'mocks/_editor.model.php';

class ModelBaseTestCase extends PrankTestCase {
	public $db = null;
	
	public function setup() {
		$this->setup_prank_spine();
		$this->db = ModelConnection::instance();
		require 'mocks/_users.table.php';
		require 'mocks/_cars.table.php';
		require 'mocks/_profiles.table.php';
		require 'mocks/_habtm.tables.php';
		require 'mocks/_editors.table.php';
	}
	
	public function teardown() {
		$this->teardown_prank_spine();
		$this->db->exec('DROP TABLE `users`;');
		$this->db->exec('DROP TABLE `cars`;');
		$this->db->exec('DROP TABLE `profiles`;');
		$this->db->exec('DROP TABLE `authors`;');
		$this->db->exec('DROP TABLE `articles`;');
		$this->db->exec('DROP TABLE `articles_authors`;');
		$this->db->exec('DROP TABLE `editors`;');
	}	
	
	public function test___construct() {
		$table = Inflector::tabelize('User');
		$this->assert_equal($table, 'users');
		
		$columns = array('id', 'email', 'password', 'name', 'admin', 'created_at');
		$columns = $this->db->columns($table);
		$this->assert_equal($columns, $columns);
		
		$test = new User;
		$this->assert_equal($test->fields(), $columns);
		$this->assert_false($test->exists());
	}
	
	public function test_has_many() {
		$user = User::find_by_name('test2');
		$this->assert_is_a($user, 'User');
		$this->assert_true($user->exists());
		$this->assert_true(isset($user->cars));
		$this->assert_is_a($user->cars, 'Collection');
		
		$this->assert_equal(count($user->cars), 5);
		foreach ($user->cars as $car) {
			$this->assert_is_a($car, 'Car');
			$this->assert_true($car->exists());
		}
	}
	
	public function test_has_one() {
		$user = User::find_by_name('test1');
		$this->assert_is_a($user, 'User');
		$this->assert_true($user->exists());
		$this->assert_true(isset($user->profile));
		$this->assert_is_a($user->profile, 'Profile');
		$this->assert_equal($user->profile->title, 'title1');
		
		$user = User::find_by_name('test2');
		$this->assert_equal($user->profile->title, 'title2');
	}
	
	public function test_belongs_to() {
		$car = Car::find_by_model('Ford1');
		$this->assert_is_a($car, 'Car');
		$this->assert_true($car->exists());
		$this->assert_true(isset($car->user));
		$this->assert_is_a($car->user, 'User');
		$this->assert_equal($car->user->name, 'test1');
		
		$car = Car::find_by_model('Audi2');
		$this->assert_true($car->exists());
		$this->assert_true(isset($car->user->name));
		$this->assert_equal($car->user->name, 'test2');
	}
	
	public function test_has_and_belongs_to_many() {
		$john = Author::find_by_name('John');
		$this->assert_is_a($john, 'Author');
		$this->assert_true($john->exists());
		$this->assert_true(isset($john->articles));
		$this->assert_is_a($john->articles, 'Collection');
		$this->assert_equal(count($john->articles), 2);
		$johns_articles = $john->articles->each(function($name) {
			return $name;
		});
		$this->assert_equal($johns_articles, a('Fantasia', 'Robinson'));
		
		$fantasia = Article::find_by_name('Fantasia');
		$this->assert_is_a($fantasia, 'Article');
		$this->assert_true($fantasia->exists());
		$this->assert_true(isset($fantasia->authors));
		$this->assert_is_a($fantasia->authors, 'Collection');
		$this->assert_equal(count($fantasia->authors), 2);
		$fantasias_authors = $fantasia->authors->each(function($name) {
			return $name;
		});
		$this->assert_equal($fantasias_authors, a('John', 'Patric'));
	}
	
	public function test_empty_relations() {
		$author = Author::find_by_name('John');
		$this->assert_is_a($author, 'Author');
		$this->assert_true(isset($author->editor));
		$this->assert_is_a($author->editor, 'Editor');
		$this->assert_false($author->editor->exists());
		$this->assert_true($author->editor->hollow());
	}
	
	public function test_filling_empty_singular_relations() {
		$author = Author::find_by_name('John');
		$this->assert_false($author->editor->exists());
		$this->assert_true($author->editor->hollow());
		$this->assert_false($author->modified());
		$editor = new Editor;
		$editor->name = 'Edward Johnson';
		$author->editor = $editor;
		$this->assert_equal($author->editor->name, 'Edward Johnson');
		$this->assert_false($author->editor->exists());
		$this->assert_false($author->editor->hollow());
		$this->assert_true($author->editor->modified());
		
		/**
		 * Here we need to loop through the Model->relations, and check if
		 * they're modifed.
		 * 
		 * What need s to be done is extending the collection, so that it has
		 * a modified status. Also, do we really need N queries for N objects?
		 */

		// TEST SAVING RELATIONS
	}
	
	public function test_no_overwriting_user_set_singular_relations() {
		$author = Author::find_by_name('John');
		$article = new Article;
		$article->name = 'Lybris';
		$article->body = 'Amazing article';
		$articles = new Collection($article);
		$author->articles = $articles;
		$this->assert_is_a($author->articles, 'Collection');
		$this->assert_equal(count($articles), 1);
		$this->assert_equal(count($author->articles), 1);
		
		$return = $author->articles->each(function($name) {return $name;});
		$this->assert_equal($return, array('Lybris'));
	}
	
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
		$this->assert_false(isset($test->id));
		$test->save();
		$time = $this->db->now();
		$this->assert_true(isset($test->id));
		
		$result = $this->db->query("select * from users where name='john doe';");
		$this->assert_equal($result->rowCount(), 1);
		$result = $result->fetch();
		$this->assert_equal($result['created_at'], $time);
		$this->assert_equal($result['updated_at'], $time);
		
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
		$test->delete();
		$this->assert_false($test->hollow());
		$this->assert_false($test->exists());
		$this->assert_true($test->modified());
		
		$result = $this->db->query("select * from users where name='test1';");
		$this->assert_equal($result->rowCount(), 0);
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