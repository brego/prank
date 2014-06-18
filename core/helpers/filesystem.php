<?php
/**
 * Filesystem helper functions
 *
 * @filesource
 * @copyright  Copyright (c) 2008-2014, Kamil "Brego" Dzieliński
 * @license    http://opensource.org/licenses/mit-license.php The MIT License
 * @author     Kamil "Brego" Dzieliński <brego@brego.dk>
 * @link       http://prank.brego.dk/ Prank's project page
 * @link       http://github.com/brego/prank/ Prank's Git repository
 * @package    Prank
 * @subpackage Helpers
 * @since      Prank 0.10
 * @version    Prank 0.75
 */

/**
 * Recursively delete a directory
 *
 * Removes specified directory/files, recursively.
 *
 * @param  string  $target Directory or file to be removed.
 * @return boolean Result of the removal.
 */
function rm($target) {
	if (is_file($target)) {
		if (is_writable($target)) {
			if (unlink($target)) {
				return true;
			}
		}
		return false;
	}
	if (is_dir($target)) {
		if (is_writable($target)) {
			foreach(new DirectoryIterator($target) as $object) {
				if ($object->isDot()) {
					unset($object);
					continue;
				}
				if ($object->isFile()) {
					rm($object->getPathName());
				} elseif ($object->isDir()) {
					rm($object->getRealPath());
				}
				unset($object);
			}
			if (rmdir($target)) {
				return true;
			}
		}
		return false;
	}
}

?>