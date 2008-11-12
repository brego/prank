<?php

session_start();
header('Content-Type: text/javascript');
ob_start('ob_gzhandler');

if (isset($_SESSION['prank']['javascript_behavior'])) {
	echo implode("\n", $_SESSION['prank']['javascript']['behaviors']);
}

ob_end_flush();

?>