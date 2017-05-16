<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Created by PhpStorm.
 * User: 11400277
 * Date: 20/04/2017
 * Time: 10:20
 */
class HomeController extends MY_Controller
{
    /**
     * MY_Controller for more information
     * @var string
     */
    protected $access = '*';

    private $dir = 'home/';

    public function index()
    {
        $this->display($this->dir . 'index');
    }

    public function page404()
    {
        $this->display($this->dir . 'page404');
    }

    public function userdata(){
        print_r($this->session->userdata());
    }

    public function des(){
        $this->session->sess_destroy();
    }
}