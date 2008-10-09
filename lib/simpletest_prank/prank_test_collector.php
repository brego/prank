<?php

class PrankTestCollector extends SimpleCollector {
	public function _handle(&$test, $file) {
		if(is_file($file)) {
			parent::_handle($test, $file);
		} elseif(is_dir($file)) {
			$file = $this->_removeTrailingSlash($file);
	        if ($handle = opendir($file)) {
	            while (($entry = readdir($handle)) !== false) {
	                if ($this->_isHidden($entry)) {
	                    continue;
	                }
	                $this->_handle($test, $file . DIRECTORY_SEPARATOR . $entry);
	            }
	            closedir($handle);
	        }
		}
	}
}

?>