<?php
/**
 * Created by PhpStorm.
 * User: 11500613
 * Date: 29/03/2017
 * Time: 11:36
 */
class Home extends MY_Controller
{
    private $dir = 'HTML/';

    public function homepage(){
       $this->display($this->dir . 'Welcome');
    }
    public function login(){
        $this->display($this->dir.'Login');
    }
    public function userpage(){
        $this ->display($this->dir.'Userpage');
    }
}
