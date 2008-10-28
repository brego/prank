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
		
		$columns = array(
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
		$this->assert_equal($this->db->columns($table), $columns);
		
		$test = new User;
		$this->assert_equal($test->fields(), $columns);
		$this->assert_false($test->exists());
		$this->assert_false($test->modified());
		$this->assert_true (isset($test->cars));
		$this->assert_false($test->cars->exists());
		$this->assert_true (isset($test->profile));
		$this->assert_false($test->profile->exists());
	}
	
	public function test_has_many() {
		$user = User::find_by_name('test2');
		$this->assert_is_a($user, 'User');
		$this->assert_true($user->exists());
		$this->assert_true(isset($user->cars));
		$this->assert_is_a($user->cars, 'ModelCollection');
		
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
		$this->assert_is_a($john->articles, 'ModelCollection');
		$this->assert_equal(count($john->articles), 2);
		$johns_articles = $john->articles->each(function($name) {
			return $name;
		});
		$this->assert_equal($johns_articles, a('Fantasia', 'Robinson'));
		
		$fantasia = Article::find_by_name('Fantasia');
		$this->assert_is_a($fantasia, 'Article');
		$this->assert_true($fantasia->exists());
		$this->assert_true(isset($fantasia->authors));
		$this->assert_is_a($fantasia->authors, 'ModelCollection');
		$this->assert_equal(count($fantasia->authors), 2);
		$fantasias_authors = $fantasia->authors->each(function($name) {
			return $name;
		});
		$this->assert_equal($fantasias_authors, a('John', 'Patric'));
	}
	
	public function test_empty_relations() {
		$author = Author::find_by_name('John');
		
		$this->assert_is_a ($author, 'Author');
		$this->assert_true (isset($author->editor));
		$this->assert_is_a ($author->editor, 'Editor');
		$this->assert_false($author->editor->exists());
		$this->assert_true ($author->editor->hollow());
	}
	
	public function test_filling_empty_has_one_relations() {
		// has_one editor
		$author = new Author;
		$author->name = 'Judith';
		
		// belongs_to author
		$editor = new Editor;
		$editor->name = 'Edward Johnson';
		
		$author->editor = $editor;
		
		$this->assert_equal($author->editor->name, 'Edward Johnson');
		$this->assert_false($author->editor->exists());
		$this->assert_false($author->editor->hollow());
		$this->assert_true ($author->editor->modified());
		
		$this->assert_true($author->save());
		$this->assert_true($author->exists());
		$this->assert_true($author->editor->exists());
		
		$db_editor = $this->db->query("select * from editors where name ='Edward Johnson';");
		$db_editor = $db_editor->fetch();
		$this->assert_true($db_editor['author_id'], $author->id);
		
		$editor = Editor::find_by_name('Edward Johnson');
		$this->assert_true ($editor->exists());
		$this->assert_equal($editor->author->name, 'Judith');
	}
	
	public function test_filling_empty_belongs_to_relations() {
		// belongs_to author
		$editor = new Editor;
		$editor->name = 'Eric';
		
		// has_one editor
		$author = new Author;
		$author->name = 'Albert';
		
		$editor->author = $author;
		
		$this->assert_true($editor->save());
		
		$this->assert_true($editor->exists());
		$this->assert_true($author->exists());
		$this->assert_true($editor->author->exists());
		
		
		$db_editor = $this->db->query("select * from editors where name ='Eric';");
		$db_editor = $db_editor->fetch();
		$this->assert_true($db_editor['author_id'], $author->id);
		
		unset($author, $editor);
		
		$author = Author::find_by_name('Albert');
		$this->assert_false($author->hollow());
		$this->assert_true (isset($author->editor));
		$this->assert_true ($author->editor->exists());
		$this->assert_false($author->editor->hollow());
		$this->assert_equal($author->editor->name, 'Eric');
	}
	
	public function test_filling_empty_has_many_relations() {
		// has_many cars
		$user = new User;
		$user->name = 'Ulric';
		
		// belongs_to user
		$car_one = new Car;
		$car_one->model = 'Lamborgini';
		
		$this->assert_false($user->cars->exists());
		$user->cars = $car_one;
		$this->assert_true (isset($user->cars));
		$this->assert_is_a ($user->cars, 'ModelCollection');
		$this->assert_equal($user->cars->relation_type(), 'has_many');
		foreach ($user->cars as $car) {
			$this->assert_equal($car->relation_type(), 'has_many');
		}
		
		$this->assert_true ($user->save());
		
		$db_cars = $this->db->query("select * from cars where model='Lamborgini';");
		foreach ($db_cars as $db_car) {
			$this->assert_equal($db_car['user_id'], $user->id);
		}
		
		$user = User::find_by_name('Ulric');
		$this->assert_true ($user->exists());
		$this->assert_equal(count($user->cars), 1);
		$this->assert_true ($user->cars->exists());
	}
	
	public function test_filling_empty_has_and_belongs_to_many_relations() {
		$author = new Author;
		$author->name = 'Archibald';
		
		$article_1 = new Article;
		$article_1->name = 'Anachronysms';
		$article_2 = new Article;
		$article_2->name = 'Antichrist';
		
		$author->articles = new ModelCollection($article_1, $article_2);
		
		$this->assert_false($author->exists());
		$this->assert_false($author->articles->exists());
		
		$this->assert_true($author->save());
		
		$db_link = $this->db->query("select * from articles_authors where author_id='".$author->id."';");
		$this->assert_equal($db_link->rowCount(), 2);
		
		unset($author, $article_1, $article_2);
		
		$author = Author::find_by_name('Archibald');
		$this->assert_true($author->exists());
		$this->assert_true($author->articles->exists());
		$this->assert_equal(count($author->articles), 2);
		
		$articles = $author->articles->each(function($name) {return $name;});
		$this->assert_equal($articles, a('Anachronysms', 'Antichrist'));
	}

	public function test_updating_has_one_relations() {
		// has_one editor
		$author = new Author;
		$author->name = 'Judith';
		
		// belongs_to author
		$editor = new Editor;
		$editor->name = 'Edward Johnson';
		
		$author->editor = $editor;
		$this->assert_true($author->save());

		$db_editor = $this->db->query("select * from editors where name='Edward Johnson';");
		$this->assert_equal($db_editor->rowCount(), 1);
		
		$db_editor = $this->db->query("select * from editors where name='Edward D. Johnson';");
		$this->assert_equal($db_editor->rowCount(), 0);
		
		$author = Author::find_by_name('Judith');
		$author->editor->name = 'Edward D. Johnson';
		$this->assert_true($author->editor->modified());
		$this->assert_true($author->editor->relation());
		$this->assert_true($author->editor->exists());
		$this->assert_false($author->editor->hollow());
		$this->assert_true($author->save());
		
		$db_editor = $this->db->query("select * from editors where name='Edward D. Johnson';");
		$this->assert_equal($db_editor->rowCount(), 1);
		$db_author = $this->db->query("select * from authors where name='Judith';");
		$this->assert_equal($db_author->rowCount(), 1);
		
		$editor = Editor::find_by_name('Edward D. Johnson');
		$this->assert_is_a($editor, 'Editor');
		$this->assert_equal($editor->author->name, 'Judith');
	}
	
	public function test_updating_belongs_to_relations() {
		// has_one editor
		$author = new Author;
		$author->name = 'Judith';
		
		// belongs_to author
		$editor = new Editor;
		$editor->name = 'Edward Johnson';
		
		$editor->author = $author;
		$this->assert_true($editor->save());
		
		$db_author = $this->db->query("select * from authors where name='Judith';");
		$this->assert_equal($db_author->rowCount(), 1);
		
		$db_author = $this->db->query("select * from authors where name='Judith Smith';");
		$this->assert_equal($db_author->rowCount(), 0);
		
		$editor = Editor::find_by_name('Edward Johnson');
		$editor->author->name = 'Judith Smith';
		$this->assert_true($editor->author->modified());
		$this->assert_true($editor->author->relation());
		$this->assert_true($editor->author->exists());
		$this->assert_true($editor->save());
		
		$db_author = $this->db->query("select * from authors where name='Judith Smith';");
		$this->assert_equal($db_author->rowCount(), 1);
		$db_editor = $this->db->query("select * from editors where name='Edward Johnson';");
		$this->assert_equal($db_editor->rowCount(), 1);
		
		$author = Author::find_by_name('Judith Smith');
		$this->assert_is_a($author, 'Author');
		$this->assert_equal($author->editor->name, 'Edward Johnson');
	}
	
	public function test_updating_has_many_relations() {
		// has_many cars
		$user = new User;
		$user->name = 'Ulric';
		
		// belongs_to user
		$car_one = new Car;
		$car_one->model = 'Lamborgini';
		$car_two = new Car;
		$car_two->model = 'Peugeot';
		
		$user->cars->add($car_one);
		$user->cars->add($car_two);
		$this->assert_true($user->save());
		
		$db_car = $this->db->query("select * from cars where model='Lamborgini' and user_id='".$user->id."';");
		$this->assert_equal($db_car->rowCount(), 1);
		$db_car = $this->db->query("select * from cars where model='Peugeot' and user_id='".$user->id."';");
		$this->assert_equal($db_car->rowCount(), 1);
		
		$user = User::find_by_name('Ulric');
		$user->cars->each(function($car) {
			if ($car->model == 'Lamborgini') {
				$car->model = 'Fiat';
			} elseif ($car->model == 'Peugeot') {
				$car->model = 'Lada';
			}
		});
		$this->assert_true($user->save());
		
		$user = User::find_by_name('Ulric');
		$cars = $user->cars->each(function($model) {return $model;});
		$this->assert_equal($cars, a('Fiat', 'Lada'));
	}
	
	public function test_updating_has_and_belongs_to_many_relations() {
		$author = new Author;
		$author->name = 'Archibald';
		
		$article_1 = new Article;
		$article_1->name = 'Anachronysms';
		$article_2 = new Article;
		$article_2->name = 'Antichrist';
		
		$author->articles = new ModelCollection($article_1, $article_2);
		
		$this->assert_true($author->save());
		
		$author = Author::find_by_name('Archibald');
		$author->articles->each(function($article) {$article->name = 'Lobster';});
		$this->assert_true($author->save());
		
		$author = Author::find_by_name('Archibald');
		$articles = $author->articles->each(function($name) {return $name;});
		$this->assert_equal($articles, a('Lobster', 'Lobster'));
	}
	
	// Resolve this problem (added to todo):
	// public function test_no_overwriting_user_set_relations() {
	// 	$author = Author::find_by_name('John');
	// 	$article = new Article;
	// 	$article->name = 'Lybris';
	// 	$article->body = 'Amazing article';
	// 	$author->articles = $article;
	// 	$this->assert_equal(count($author->articles), 1);
	// 	$this->assert_true($author->save());
	// 	
	// 	$author = Author::find_by_name('John');
	// 	$return = $author->articles->each(function($name) {return $name;});
	// 	$this->assert_equal($return, array('Lybris')); //This is wrong, it should be right...
	// }
	
	public function test_perpetuum_relations() {
		$user = User::find_by_name('test1');
		$this->assert_true(isset($user->profile->user->profile));
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
	
	// public function test_validation_setup() {
	// 	$article = new Article;
	// 	$validations = array(
	// 		'name' => array(
	// 			'validates_presence_of' => array(),
	// 			'validates_length_of'   => array(
	// 				'min'=>2,
	// 				'max'=>40
	// 				)
	// 			)
	// 		);
	// 	$this->assert_equal($article->validations(), $validations);
	// }

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
		$test = User::find(1);
		$this->assert_is_a($test, 'User');
		$this->assert_equal($test->name, 'test1');
		
		$test = User::find_all();
		$this->assert_is_a($test, 'ModelCollection');
		$this->assert_equal(count($test), 2);
		
		$test = User::find_all_and_order_by_name_desc();
		$this->assert_is_a($test, 'ModelCollection');
		$this->assert_equal(count($test), 2);
		$result = $test->each(function($name) {return $name;});
		$this->assert_equal($result, a('test2', 'test1'));
		
		$test = User::find_all_and_order_by_name_desc_and_email();
		$this->assert_is_a($test, 'ModelCollection');
		$this->assert_equal(count($test), 2);
		$result = $test->each(function($name) {return $name;});
		$this->assert_equal($result, a('test2', 'test1'));
		
		$test = User::find_by_name('test1');
		$this->assert_is_a($test, 'User');
		$this->assert_equal($test->name, 'test1');
		
		$test = User::find_by_email('test1@email.com');
		$this->assert_is_a($test, 'User');
		$this->assert_equal($test->name, 'test1');
		
		$test = User::find_by_email_and_name('test1@email.com', 'test1');
		$this->assert_is_a($test, 'User');
		$this->assert_equal($test->email, 'test1@email.com');
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