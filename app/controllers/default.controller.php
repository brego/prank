<?php

class DefaultController extends Prank::Controller::Base {
	public function index() {
		
		$this->posts = Post::find_all();
	}
	
	public function do_some_thing($string = '')
	{
		$this->view   = false;
		$this->layout = false;
		print 'I am the default controller, and the do_some_thing function...';
		if ($string != '') {
			print ' And we need to say '.$string.'!';
		}
	}
}

?>