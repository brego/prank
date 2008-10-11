<?php

$development = array();
$development['type']     = 'mysql';
$development['host']     = 'localhost';
$development['user']     = 'root';
$development['password'] = '';
$development['db']       = 'prank_development';

$test = array();
$test['type']     = 'mysql';
$test['host']     = 'localhost';
$test['user']     = 'root';
$test['password'] = '';
$test['db']       = 'prank_test';

$production = array();
$production['type']     = '';
$production['host']     = '';
$production['user']     = '';
$production['password'] = '';
$production['db']       = '';

$db = array();
$db['db']['development'] = $development;
$db['db']['test']        = $test;
$db['db']['production']  = $production;
c($db);
?>