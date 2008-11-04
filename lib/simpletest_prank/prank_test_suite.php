<?php

require_once ROOT.'core'.DS.'base.php';

class PrankTestSuite extends TestSuite {
	public function __construct($label=false) {
		$this->rm(ROOT.'tests'.DS.'tmp'.DS);
		mkdir(ROOT.'tests'.DS.'tmp'.DS);
		
		parent::__construct($label);
	}
	
	private function rm($target) {
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
						$this->rm($object->getPathName());
					} elseif ($object->isDir()) {
						$this->rm($object->getRealPath());
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
}

?>