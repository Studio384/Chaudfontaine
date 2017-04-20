<?php

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
}