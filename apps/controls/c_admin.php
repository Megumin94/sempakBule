<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class c_admin extends Null_Controller {
    public function __construct() {
        parent::__construct();
       
    }

    function index(){
    	 $this->load->view('login');
    }

    function loginProcess(){
    	
    }

}
?>