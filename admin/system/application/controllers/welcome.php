<?php

class Welcome extends Controller {

	function Welcome()
	{
		parent::Controller();	
	}
	
	function index()
	{
		$data = array('title' => 'phpIPN Administration Panel');
		$this->load->view('header', $data);
		$this->load->view('welcome_message', $data);
		$this->load->view('header', $data);
	}
}

/* End of file welcome.php */
/* Location: ./system/application/controllers/welcome.php */