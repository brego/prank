<?php

class DefaultController extends ControllerBase {
	public function index() {
		
		$this->posts = Post::find_all();
	}
	
	public function do_some_thing($string = '')
	{
		$this->view   = false;
		$this->layout = false;
		echo 'I am the default controller, and the do_some_thing function...';
		if ($string != '') {
			echo ' And we need to say '.$string.'!';
		}
	}
}

?>