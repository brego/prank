<?php

class Http404Controller extends ControllerBase {
	public function index() {
		$this->view   = false;
		$this->layout = false;
		header('Status: 404 Not Found', true, 404);
?>
<!doctype html>
<html lang="en-US">
	<head>
		<title>404 - Ressource not found</title>
	</head>
	<body>
		<h1>404 - Ressource not found</h1>
		<p>Requested ressource was not found.</p>
	</body>
</html>
<?php
	}
}

?>