<?php

$map->connect(':controller/:action/:id', array('controller' => 'default', 'action' => 'index'));

// Future:
//$map->connect(':controller/:action/:id.:format');
//$map->root(array('controller' => 'welcome'))

?>