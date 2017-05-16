<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Created by PhpStorm.
 * User: 11400277
 * Date: 9/06/2016
 * Time: 16:05
 */
class UserController extends MY_Controller
{
    /**
     * MY_Controller for more information
     * @var string
     */
    protected $access = '@';

    /**
     * Delete all sessions
     */
    public function logout()
    {
        $this->load->model('AuthModel', 'auth');
        $this->auth->logout();
        redirect();
    }
}