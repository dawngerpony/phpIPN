<?php

class Welcome extends Controller {

	function Welcome()
	{
		parent::Controller();	
        $this->load->helper('url');
        $this->load->helper('html');
	}
	
	function index()
	{
		$data = array('title' => 'home');
		$this->load->view('header', $data);
		$this->load->view('welcome_message', $data);
		$this->load->view('footer', $data);
	}
}

/* End of file welcome.php */
/* Location: ./system/application/controllers/welcome.php */