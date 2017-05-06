<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AuthController extends MY_Controller {
    private $dir = 'auth/';

    // Validate login data
    public function login()
    {
        $this->form_validation->set_rules('email', '', 'trim|required|valid_email');
        $this->form_validation->set_rules('password', '', 'trim|required');

        if ($this->form_validation->run() == true) {
            $email = $this->input->post('email');
            $password = $this->input->post('password');

            $this->load->model('AuthModel', 'auth');
            $status = $this->auth->login($email, $password);

            if ($status == EXIT_ERROR) {
                $data['error'] = 'Gebruikersnaam en/of wachtwoord is fout.';
            } elseif ($status == EXIT_ERROR_ACCOUNT_BLOCKED) {
                $data['error'] = 'Dit account is nog niet geactiveerd. Activeer het via de email.';
            } elseif ($status == EXIT_SUCCESS) {
                $this->session->set_userdata($this->auth->get_data());

                $this->session->set_flashdata('main_success', $this->lang->line('login_success'));
                redirect(base_url());
            }
        }

        $this->display($this->dir . 'login', (isset($data)) ? $data : array());
    }

    // Register a new user
    public function register()
    {
        $this->form_validation->set_rules('email', '', 'trim|required|valid_email|max_length[100]|is_unique[users.email]');
        $this->form_validation->set_rules('first_name', '', 'trim|required|max_length[50]');
        $this->form_validation->set_rules('last_name', '', 'trim|required|max_length[50]');

        if ($this->form_validation->run() == true) {
            $email = $this->input->post('email');
            $first_name = $this->input->post('first_name');
            $last_name = $this->input->post('last_name');

            $this->load->model('AuthModel', 'auth');
            $status = $this->auth->register($email, $first_name, $last_name);

            if ($status == EXIT_ERROR) {
                $data['error'] = 'Email niet verzonden';
            } elseif ($status == EXIT_SUCCESS) {
                $this->session->set_flashdata('main_success', 'Registratie voltooid');
                redirect(base_url());
            }
        }

        $this->display($this->dir . 'register', (isset($data)) ? $data : array());
    }

    /// Activate a new account
    public function activate($code)
    {
        $this->form_validation->set_rules('new_password', '', 'trim|required');
        $this->form_validation->set_rules('new_passconf', '', 'trim|required|matches[new_password]');

        if ($this->form_validation->run() == true) {
            $new_password = $this->input->post('new_password');

            $this->load->model('AuthModel', 'auth');
            $status = $this->auth->activate($code, $new_password);

            if ($status == EXIT_ERROR) {
                $data['error'] = 'Activatie kon niet worden voltooid';
            } elseif ($status == EXIT_SUCCESS) {
                $userdata = $this->auth->get_data();
                $private_key = $userdata['private_key'];
                unset($userdata['private_key']);
                $this->session->set_userdata($userdata);

                $this->session->set_flashdata('main_success', 'Activatie voltooid');
                force_download('private_key.txt', $private_key);
                redirect(base_url());
            }
        }

        $this->display($this->dir . 'activate', (isset($data)) ? $data : array());
    }
}