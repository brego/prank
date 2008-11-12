<?php

if (count($argv) < 2) {
	echo "You need to provide at least one valid option\n";
	return 0;
	die();
}

define('DS',   DIRECTORY_SEPARATOR);
define('ROOT', dirname(dirname(__FILE__)).DS);

$cwd         = getcwd();
$methods     = array('add', 'test', 'help', 'test_mate');
$description = array(
	'add'  => 'Adds a file',
	'test' => 'Runs the test suite',
	'help' => 'Shows this help');

if (array_search($argv[1], $methods) !== false) {
	$method = $argv[1];
	unset($argv[0], $argv[1]);
	call_user_func_array($method, $argv);
} else {
	echo "You need to provide a valid option.\n";
}

function add() {
	$args = func_get_args();
	if (count($args) >= 2) {
		$method = 'add_'.$args[0];
		if (function_exists($method)) {
			echo 'Adding new '.$args[0]." - ".$args[1]."\n";
			unset($args[0]);
			call_user_func_array($method, $args);
		} else {
			echo "Add method ".$args[0]." not found.\n";
		}
	} else {
		echo "Add needs at least two parameters.\n";
	}
}

function add_test($file, $force=false) {
	
	function __autoload($class_name) {
		$class_name = strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $class_name));
		$class_name = str_replace('_', DS, $class_name);

		if(is_file(ROOT.'core'.DS.$class_name.'.php')) {
			require_once ROOT.'core'.DS.$class_name.'.php';
		}
	}
	
	
	$test_file = ROOT.'tests'.DS.$file;
	$test_file = str_replace('.php', '.test.php', $test_file);
	if (!is_file($test_file) || $force !== false) {
		$tests     = '';
		$classes   = get_declared_classes();
		$functions = get_defined_functions();
		require_once ROOT.$file;
		$classes   = array_diff(get_declared_classes(), $classes);
		$functions_new = get_defined_functions();
		$functions = array_diff($functions_new['user'], $functions['user']);
		
		foreach ($classes as $class) {
			$class_reflection = new ReflectionClass($class);
			foreach ($class_reflection->getMethods() as $method) {
				$tests .= "\n\tpublic function test_".$method->getName()."() {\n\t}\n";
			}
		}
		
		foreach ($functions as $function) {
			$tests .= "\n\tpublic function test_".$function."() {\n\t}\n";
		}
		
		$directory_count = explode(DS, $file);
		$require         = 'dirname(__FILE__)';
		foreach ($directory_count as $value) {
			$require = 'dirname('.$require.')';
		}
		
		$template  = file_get_contents(ROOT.'scripts'.DS.'templates'.DS.'new_test.tpl');
		$classname = str_replace(' ', '', ucwords(str_replace(DS, ' ', str_replace('.php', '', $file))));
		$classname = str_replace('Core', '', $classname);
		$tags = array(
			'/**REQUIRE**/'       => $require,
			'/**CLASSFILE**/'     => $file,
			'/**CLASSFILENAME**/' => $classname,
			'/**TESTS**/'         => $tests);
		$template = str_replace(array_keys($tags), $tags, $template);
		
		file_put_contents($test_file, $template);
		
		echo "Test file created at $file\n";
	} else {
		echo "Test file already exists - run with 'force' parameter to overwrite\n";
	}
}

function test_mate() {
	return test(false, false, 'mate');
}

function test($case=false, $method=false, $mate=false) {
	// SimpleTest has it's issues. But we still love it :P
	error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
	
	$case   = $case   === false ? false : $case.'TestCase';
	$method = $method === false ? false : 'test_'.$method;
	
	require_once ROOT.'lib'.DS.'simpletest'.DS.'unit_tester.php';
	require_once ROOT.'lib'.DS.'simpletest'.DS.'collector.php';
	require_once ROOT.'lib'.DS.'simpletest_prank'.DS.'prank_test_collector.php';
	require_once ROOT.'lib'.DS.'simpletest_prank'.DS.'prank_test_case.php';
	require_once ROOT.'lib'.DS.'simpletest_prank'.DS.'prank_test_suite.php';
	
	$test = new PrankTestSuite('[Prank Test Suite] '.($case?:'').' '.($method?:''));
	$test->collect(ROOT.'tests/', new PrankTestCollector);

	if ($mate == 'mate') {
		session_start();
		$reporter = new HtmlReporter;
	} else {
		$reporter = new DefaultReporter;
	}

	$result = $test->run(new SelectiveReporter($reporter, $case, $method));
	
	return ($result ? 0 : 1);
}

function help() {
	echo "You can always run 'prank [option] help' for aditional info.\n",	
		"Valid options are:\n";
	foreach($GLOBALS['methods'] as $option) {
		echo " ".$option.":\t".$GLOBALS['description'][$option]."\n";
	}
}

?>