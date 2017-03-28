<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends MY_Controller {

    /**
     * Directory for the pages
     * @var string
     */
    private $dir = 'welcome/';

	public function index()
	{
        $this->display($this->dir . 'index');
	}
}
