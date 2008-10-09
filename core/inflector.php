<?php
/**
 * Inflector - inflects words.
 *
 * Pluralizes, singularizes and changes words. Based on CakePHP's Inflector
 * class.
 *
 * PHP version 5.3.
 *
 * @filesource
 * @copyright  Copyright (c) 2008, Kamil "Brego" Dzieliński
 * @license    http://opensource.org/licenses/mit-license.php The MIT License
 * @author     Kamil "Brego" Dzieliński <brego@brego.dk>
 * @link       http://prank.brego.dk Prank's project page
 * @link       http://cakephp.org CakePHP's project page
 * @package    Prank
 * @subpackage Core
 * @since      Prank 0.10
 * @version    Prank 0.10
 */

class Inflector {
	
	private function __construct() {}
	
/**
 * Return $word in plural form.
 *
 * @param  string $word Word in singular
 * @return string Word in plural
 */
	public static function pluralize($word)	{
		$core_plural_rules = array(
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
			'/$/'                      => 's');

		$core_uninflected_plural = array('.*[nrlm]ese', '.*deer', '.*fish',
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
			'whiting', 'wildebeest', 'Yengeese');

			$core_irregular_plural = array(
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
				'turf'      => 'turfs');

		$plural_rules = $core_plural_rules;
		$uninflected  = $core_uninflected_plural;
		$irregular    = $core_irregular_plural;

		$regex_uninflected = self::enclose(join('|', $uninflected));
		$regex_irregular   = self::enclose(join('|', array_keys($irregular)));

		if (preg_match('/^('.$regex_uninflected.')$/i', $word, $regs)) {
			return $word;
		}

		if (preg_match('/(.*)\\b('.$regex_irregular.')$/i', $word, $regs)) {
			return $regs[1] . $irregular[strtolower($regs[2])];
		}

		foreach($plural_rules as $rule => $replacement) {
			if (preg_match($rule, $word)) {
				return preg_replace($rule, $replacement, $word);
			}
		}
		return $word;
	}

/**
 * Return $word in singular form.
 *
 * @param  string $word Word in plural
 * @return string Word in singular
 */
	public static function singularize($word)
	{
		$core_singular_rules = array(
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
			'/s$/i'                 => '');

		$core_uninflected_singular = array('.*[nrlm]ese', '.*deer', '.*fish',
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
			'whiting', 'wildebeest', 'Yengeese');

		$core_irregular_singular = array(
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
			'turfs'       => 'turf');

		$singular_rules = $core_singular_rules;
		$uninflected    = $core_uninflected_singular;
		$irregular      = $core_irregular_singular;

		$regex_uninflected = self::enclose(join('|', $uninflected));
		$regex_irregular   = self::enclose(join('|', array_keys($irregular)));

		if (preg_match('/^('.$regex_uninflected.')$/i', $word, $regs)) {
			return $word;
		}

		if (preg_match('/(.*)\\b('.$regex_irregular . ')$/i', $word, $regs)) {
			return $regs[1].$irregular[strtolower($regs[2])];
		}

		foreach ($singular_rules as $rule => $replacement) {
			if (preg_match($rule, $word)) {
				return preg_replace($rule, $replacement, $word);
			}
		}
		return $word;
	}

/**
 * Returns given lower_case_and_underscored_word as a CamelCased word.
 *
 * @param  string $word Word to camelize
 * @return string Camelized word. LikeThis.
 */
	public static function camelize($word)
	{
		return str_replace(" ", "", ucwords(str_replace("_", " ", $word)));
	}

/**
 * Returns an underscore-syntaxed (like_this) version of the CamelCased word.
 *
 * @param  string $word CamelCased word to be "underscorized"
 * @return string Underscore syntaxed version of the $word
 */
	public static function underscore($word)
	{
		return strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $word));
	}

/**
 * Returns a human-readable string from $word, by replacing underscores with a
 * space, and by upper-casing the initial character.
 *
 * @param  string $word String to be made more readable
 * @return string Human-readable string
 */
	public static function humanize($word)
	{
		return ucfirst(str_replace("_", " ", $word));
	}

/**
 * Returns model class name ("Post" for the database table "posts".) for given
 * database table.
 *
 * @param  string $tableName Name of database table to get class name for
 * @return string Singularized and Camelized $word
 */
	public static function modelize($tableName)
	{
		return self::camelize(self::singularize($tableName));
	}

/**
 * Returns corresponding table name for given $className. ("posts" for the model
 * class "Posts" or controller class "Post").
 *
 * @param  string $className Name of class to get database table name for
 * @return string Name of the database table for given class
 */
	public static function tabelize($className)
	{
		return self::pluralize(self::underscore($className));
	}

/**
 * Returns corresponding controller name for given $word. ("Post" for the model
 * class "Posts" or table "posts").
 *
 * @param  string $word Name of class to get database table name for
 * @return string Singularized and camelized $word
 */
	public static function controlize($className)
	{
		return self::camelize(self::singularize($className));
	}

	private static function enclose($string)
	{
		return '(?:' . $string . ')';
	}
}
?>