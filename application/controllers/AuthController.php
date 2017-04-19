<?php

if ( ! defined( 'BASEPATH' ) ) exit( 'No direct script access allowed' );

class AuthController extends CI_Controller {
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

            $this->language_load($this->dir . 'login');

            if ($status == EXIT_ERROR) {
                $data['error'] = $this->lang->line('login_error_input');
            } elseif ($status == EXIT_ERROR_ACCOUNT_BLOCKED) {
                $data['error'] = $this->lang->line('login_error_blocked');
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
        $this->form_validation->set_rules('given_name', '', 'trim|required|max_length[50]');
        $this->form_validation->set_rules('family_name', '', 'trim|required|max_length[50]');

        if ($this->form_validation->run() == true) {
            $email = $this->input->post('email');
            $given_name = $this->input->post('given_name');
            $family_name = $this->input->post('family_name');

            $this->load->model('AuthModel', 'auth');
            $status = $this->auth->register($email, $given_name, $family_name);

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
                $this->session->set_userdata($this->auth->get_data());

                $this->session->set_flashdata('main_success', 'Activatie voltooid');
                redirect(base_url());
            }
        }

        $this->display($this->dir . 'activate', (isset($data)) ? $data : array());
    }

    // Reset the password
    public function reset_password()
    {
        $this->display($this->dir . 'reset_password');
    }
}