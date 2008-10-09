<?php

/*error_reporting(E_ALL | E_NOTICE);

define('DS',   DIRECTORY_SEPARATOR);
define('ROOT', dirname(dirname(dirname(__FILE__))).DS);
define('APP',  dirname(dirname(__FILE__)).DS);

define('MODELS',      APP.'models'.DS);
define('VIEWS',       APP.'views'.DS);
define('CONTROLLERS', APP.'controllers'.DS);
define('CONFIG',      APP.'config'.DS);
define('WEBROOT',     APP.'webroot'.DS);

define('CORE',    ROOT.'core'.DS);

require_once CORE.'base.php';
require_once CORE.'boot.php';

///////////////////////////////////////*/

require_once '/Users/brego/Sites/prank/core/boot.php';
Boot::run(__FILE__);

?>