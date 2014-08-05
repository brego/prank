<?php
/**
 * Inflector - inflects words.
 *
 * Pluralizes, singularizes and changes words. Based on CakePHP's Inflector
 * class - http://git.io/z4_CaQ
 *
 * @filesource
 * @copyright  Copyright (c) 2008-2014, Kamil "Brego" Dzieliński
 * @license    http://opensource.org/licenses/mit-license.php The MIT License
 * @author     Kamil "Brego" Dzieliński <brego@brego.dk>
 * @link       http://prank.brego.dk/ Prank's project page
 * @link       http://github.com/brego/prank/ Prank's Git repository
 * @link       http://cakephp.org/ CakePHP's project page
 * @package    Prank
 * @subpackage Core
 * @since      Prank 0.10
 * @version    Prank 0.75
 */

/**
 * Return $string in plural form.
 *
 * @param  string $string Word in singular
 * @return string Word in plural
 */
function pluralize($string)	{
	$core_plural_rules = [
		'/(s)tatus$/i'             => '\1\2tatuses',
		'/^(ox)$/i'                => '\1\2en',
		'/([m|l])ouse$/i'          => '\1ice',
		'/(matr|vert|ind)ix|ex$/i' => '\1ices',
		'/(x|ch|ss|sh)$/i'         => '\1es',
		'/([^aeiouy]|qu)y$/i'      => '\1ies',
		'/(hive)$/i'               => '\1s',
		'/(?:([^f])fe|([lr])f)$/i' => '\1\2ves',
		'/sis$/i'                  => 'ses',
		'/([ti])um$/i'             => '\1a',
		'/(p)erson$/i'             => '\1eople',
		'/(m)an$/i'                => '\1en',
		'/(c)hild$/i'              => '\1hildren',
		'/(buffal|tomat)o$/i'      => '\1\2oes',
		'/(bu)s$/i'                => '\1\2ses',
		'/(alias)/i'               => '\1es',
		'/(octop|vir)us$/i'        => '\1i',
		'/(ax|cri|test)is$/i'      => '\1es',
		'/s$/'                     => 's',
		'/$/'                      => 's'];

	$core_uninflected_plural = ['.*[nrlm]ese', '.*deer', '.*fish',
		'.*measles', '.*ois', '.*pox', '.*rice', '.*sheep', 'Amoyese',
		'bison', 'Borghese', 'bream', 'breeches', 'britches', 'buffalo',
		'cantus', 'carp', 'chassis', 'clippers', 'cod', 'coitus',
		'Congoese',	'contretemps', 'corps', 'debris', 'diabetes', 'djinn',
		'eland', 'elk',	'equipment', 'Faroese', 'flounder', 'Foochowese',
		'gallows', 'Genevese', 'Genoese', 'Gilbertese', 'graffiti',
		'headquarters', 'herpes', 'hijinks', 'Hottentotese', 'information',
		'innings', 'jackanapes', 'Kiplingese', 'Kongoese', 'Lucchese',
		'mackerel', 'Maltese', 'mews', 'moose', 'mumps', 'Nankingese',
		'news', 'nexus', 'Niasese', 'Pekingese', 'Piedmontese', 'pincers',
		'Pistoiese', 'pliers', 'Portuguese', 'proceedings', 'rabies',
		'rhinoceros', 'salmon', 'Sarawakese', 'scissors', 'sea[- ]bass',
		'series', 'Shavese', 'shears', 'siemens', 'species', 'swine',
		'testes', 'trousers', 'trout', 'tuna', 'Vermontese', 'Wenchowese',
		'whiting', 'wildebeest', 'Yengeese'];

		$core_irregular_plural = [
			'atlas'     => 'atlases',
			'beef'      => 'beefs',
			'brother'   => 'brothers',
			'child'     => 'children',
			'corpus'    => 'corpuses',
			'cow'       => 'cows',
			'ganglion'  => 'ganglions',
			'genie'     => 'genies',
			'genus'     => 'genera',
			'graffito'  => 'graffiti',
			'hoof'      => 'hoofs',
			'loaf'      => 'loaves',
			'man'       => 'men',
			'money'     => 'monies',
			'mongoose'  => 'mongooses',
			'move'      => 'moves',
			'mythos'    => 'mythoi',
			'numen'     => 'numina',
			'occiput'   => 'occiputs',
			'octopus'   => 'octopuses',
			'opus'      => 'opuses',
			'ox'        => 'oxen',
			'penis'     => 'penises',
			'person'    => 'people',
			'sex'       => 'sexes',
			'soliloquy' => 'soliloquies',
			'testis'    => 'testes',
			'trilby'    => 'trilbys',
			'turf'      => 'turfs'];

	$plural_rules = $core_plural_rules;
	$uninflected  = $core_uninflected_plural;
	$irregular    = $core_irregular_plural;
	
	$regex_uninflected = '(?:'.join('|', $uninflected).')';
	$regex_irregular   = '(?:'.join('|', array_keys($irregular)).')';

	if (preg_match('/^('.$regex_uninflected.')$/i', $string, $regs)) {
		return $string;
	}

	if (preg_match('/(.*)\\b('.$regex_irregular.')$/i', $string, $regs)) {
		return $regs[1] . $irregular[strtolower($regs[2])];
	}

	foreach($plural_rules as $rule => $replacement) {
		if (preg_match($rule, $string)) {
			return preg_replace($rule, $replacement, $string);
		}
	}
	return $string;
}

/**
 * Return $string in singular form.
 *
 * @param  string $string Word in plural
 * @return string Word in singular
 */
function singularize($string)
{
	$core_singular_rules = [
		'/(s)tatuses$/i'        => '\1\2tatus',
		'/(matr)ices$/i'        => '\1ix',
		'/(vert|ind)ices$/i'    => '\1ex',
		'/^(ox)en/i'            => '\1',
		'/(alias)es$/i'         => '\1',
		'/([octop|vir])i$/i'    => '\1us',
		'/(cris|ax|test)es$/i'  => '\1is',
		'/(shoe)s$/i'           => '\1',
		'/(o)es$/i'             => '\1',
		'/(bus)es$/i'           => '\1',
		'/([m|l])ice$/i'        => '\1ouse',
		'/(x|ch|ss|sh)es$/i'    => '\1',
		'/(m)ovies$/i'          => '\1\2ovie',
		'/(s)eries$/i'          => '\1\2eries',
		'/([^aeiouy]|qu)ies$/i' => '\1y',
		'/([lr])ves$/i'         => '\1f',
		'/(tive)s$/i'           => '\1',
		'/(hive)s$/i'           => '\1',
		'/(drive)s$/i'          => '\1',
		'/([^f])ves$/i'         => '\1fe',
		'/(^analy)ses$/i'       => '\1sis',
		'/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '\1\2sis',
		'/([ti])a$/i'           => '\1um',
		'/(p)eople$/i'          => '\1\2erson',
		'/(m)en$/i'             => '\1an',
		'/(c)hildren$/i'        => '\1\2hild',
		'/(n)ews$/i'            => '\1\2ews',
		'/s$/i'                 => ''];

	$core_uninflected_singular = ['.*[nrlm]ese', '.*deer', '.*fish',
		'.*measles', '.*ois', '.*pox', '.*rice', '.*sheep', 'Amoyese',
		'bison', 'Borghese', 'bream', 'breeches', 'britches', 'buffalo',
		'cantus', 'carp', 'chassis', 'clippers', 'cod', 'coitus',
		'Congoese', 'contretemps', 'corps', 'debris', 'diabetes', 'djinn',
		'eland', 'elk', 'equipment', 'Faroese', 'flounder', 'Foochowese',
		'gallows', 'Genevese', 'Genoese', 'Gilbertese', 'graffiti',
		'headquarters', 'herpes','hijinks', 'Hottentotese', 'information',
		'innings', 'jackanapes', 'Kiplingese', 'Kongoese', 'Lucchese',
		'mackerel', 'Maltese', 'mews', 'moose', 'mumps', 'Nankingese',
		'news', 'nexus', 'Niasese', 'Pekingese', 'Piedmontese', 'pincers',
		'Pistoiese', 'pliers', 'Portuguese', 'proceedings', 'rabies',
		'rhinoceros', 'salmon', 'Sarawakese', 'scissors', 'sea[- ]bass',
		'series', 'Shavese', 'shears', 'siemens', 'species', 'swine',
		'testes', 'trousers', 'trout', 'tuna', 'Vermontese', 'Wenchowese',
		'whiting', 'wildebeest', 'Yengeese'];

	$core_irregular_singular = [
		'atlases'     => 'atlas',
		'beefs'       => 'beef',
		'brothers'    => 'brother',
		'children'    => 'child',
		'corpuses'    => 'corpus',
		'cows'        => 'cow',
		'ganglions'   => 'ganglion',
		'genies'      => 'genie',
		'genera'      => 'genus',
		'graffiti'    => 'graffito',
		'hoofs'       => 'hoof',
		'loaves'      => 'loaf',
		'men'         => 'man',
		'monies'      => 'money',
		'mongooses'   => 'mongoose',
		'moves'       => 'move',
		'mythoi'      => 'mythos',
		'numina'      => 'numen',
		'occiputs'    => 'occiput',
		'octopuses'   => 'octopus',
		'opuses'      => 'opus',
		'oxen'        => 'ox',
		'penises'     => 'penis',
		'people'      => 'person',
		'sexes'       => 'sex',
		'soliloquies' => 'soliloquy',
		'testes'      => 'testis',
		'trilbys'     => 'trilby',
		'turfs'       => 'turf'];

	$singular_rules = $core_singular_rules;
	$uninflected    = $core_uninflected_singular;
	$irregular      = $core_irregular_singular;
	
	$regex_uninflected = '(?:'.join('|', $uninflected).')';
	$regex_irregular   = '(?:'.join('|', array_keys($irregular)).')';

	if (preg_match('/^('.$regex_uninflected.')$/i', $string, $regs)) {
		return $string;
	}

	if (preg_match('/(.*)\\b('.$regex_irregular . ')$/i', $string, $regs)) {
		return $regs[1].$irregular[strtolower($regs[2])];
	}

	foreach ($singular_rules as $rule => $replacement) {
		if (preg_match($rule, $string)) {
			return preg_replace($rule, $replacement, $string);
		}
	}
	return $string;
}

/**
 * Returns given string as CamelCase
 *
 * String an be underscored, or spaced.
 *
 * @param  string $string String to camelize
 * @return string Camelized word - LikeThis
 */
function camelcase($string) {
	return str_replace(" ", "", ucwords(str_replace("_", " ", strtolower($string))));
}

/**
 * Returns given string as camelBack
 *
 * String an be underscored, or spaced.
 *
 * @param  string $string String to camelback
 * @return string Camelbacked word - likeThis
 */
function camelback($string) {
	$string = str_replace(" ", "", ucwords(str_replace("_", " ", strtolower($string))));
	$replace = strtolower(substr($string, 0, 1));
	return substr_replace($string, $replace, 0, 1);
}

/**
 * Returns the string underscored
 *
 * @param  string $string String to be underscored
 * @return string Underscored version of the $string
 */
function underscore($string) {
	if (strpos($string, ' ') !== false) {
		return strtolower(str_replace(' ', '_', $string));
	} else {
		return strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $string));
	}
}

/**
 * Returns a human-readable string from $string, by replacing underscores with
 * a space, and by upper-casing the initial character. Whole string, apart from
 * the initial character, are lower-cased. CamelCased strings are split by
 * a space.
 *
 * @param  string $string String to be made more readable
 * @return string Human-readable string
 */
function human($string) {
	if (strpos($string, '_') !== false) {
		return ucfirst(str_replace("_", " ", strtolower($string)));
	} elseif (strpos($string, ' ') !== false) {
		return ucfirst(strtolower($string));
	} else {
		return ucfirst(strtolower(preg_replace('/(?<=\\w)([A-Z])/', ' \\1', $string)));
	}
}

/**
 * Returns model class name ("Post" for the database table "posts") for given
 * database table name.
 *
 * @param  string $table_name Name of database table to get class name for
 * @return string Singularized and Camelized $class_name
 */
function to_model($table_name) {
	return camelcase(singularize($table_name));
}

/**
 * Returns corresponding table name for given $class_name. For example "posts" for the model
 * class "Post".
 *
 * @param  string $class_name Name of class to get database table name for
 * @return string Name of the database table for given class
 */
function to_table($class_name) {
	return pluralize(underscore($class_name));
}

/**
 * Returns corresponding controller name for given $string.
 *
 * @param  string $class_name
 * @return string Singularized and camelized $class_name
 */
function to_controller($class_name)
{
	return camelcase(singularize($class_name)).'Controller';
}

?>