<?php

$map->connect(':controller/:action/:id');
$map->connect('/', array('controller' => 'default', 'action' => 'index'));

//$map->connect(':controller/:action/:id.:format');
//$map->root(array('controller' => 'welcome'))

?>