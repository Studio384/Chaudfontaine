<?php
/**
 * Created by PhpStorm.
 * User: 11400277
 * Date: 28/03/2017
 * Time: 19:55
 */
class MY_Controller extends CI_Controller
{
    /**
     * '*' all users
     * '@' logged in user
     * '/' not logged in users
     * 'A' admin
     * @var string
     */
    protected $access = '*';

    /**
     * MY_Controller constructor.
     * Uses private login_check
     */
    public function __construct()
    {
        parent::__construct();

        // Check if user is logged in
        $this->login_check();
    }

    /**
     * View builder
     * @param $url
     * @param array $data
     */
    protected function display($url, $data = array())
    {
        $this->twig->addGlobal('session', $this->session);
        $this->twig->display($url, $data);
    }

    /**
     * Checks if a user is logged in.
     * Check the permissions of the user.
     * Uses private permission_check
     * Uses models AuthModel->get_access_token()
     * Uses models AuthModel->logout()
     */
    private function login_check()
    {
        // check if access token matches database access token
        $this->load->model('AuthModel', 'auth');
        if ($this->session->userdata('access_token') && $this->session->userdata('access_token') != $this->auth->get_access_token()) {
            $this->auth->logout();
            $this->session->set_flashdata('main_error', 'Er is iets fout bij het inloggen. Log nog eens in A.U.B');
            redirect('login');
        }

        // Check page access
        if ($this->access != '/') {
            if ($this->access != '*') {
                // check the role of the user
                if (!$this->permission_check()) {
                    redirect('404');
                }

                // check if user is logged in, if not redirect
                if (!$this->session->userdata('access_token')) {
                    $this->session->set_flashdata('main_error', 'Voor deze pagina moet je ingelogd zijn.');
                    redirect('login');
                }
            }
        } else {
            if ($this->session->userdata("access_token")) {
                redirect('404');
            }
        }
    }

    /**
     * Check user is allowed to view the page
     * @return bool
     */
    private function permission_check()
    {
        if ($this->access == '@') {
            return true;
        } else {
            $access = is_array($this->access) ?
                $this->access :
                explode(',', $this->access);
            if (in_array($this->session->userdata('role'), array_map('trim', $access))) {
                return true;
            }
            return false;
        }
    }
}