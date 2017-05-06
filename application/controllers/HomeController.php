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
    private $dir = 'home/';

    public function index()
    {
        $this->display($this->dir . 'index');
    }

    public function login(){
        $this->display($this->dir.'Login');
    }
    public function userpage(){
        $this ->display($this->dir.'Userpage');
    }
    public function createAccount(){
        $this->display($this->dir.'CreateAccount');
    }

    public function userdata(){
        print_r($this->session->userdata());
    }

    public function des(){
        $this->session->sess_destroy();
    }
}