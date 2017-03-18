<?php

/**
 * Created by PhpStorm.
 * User: 11400277
 * Date: 18/03/2017
 * Time: 14:57
 */
class Upload extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('form', 'url'));
    }

    public function index()
    {
        $this->load->view('upload_form', array('error' => ' '));
    }

    public function do_upload()
    {
        $config['upload_path'] = './uploaded_files/';
        $config['allowed_types'] = 'zip';

        //$config['max_size'] = 100;
        //$config['max_width'] = 1024;
        //$config['max_height'] = 768;


        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('userfile')) {
            $error = array('error' => $this->upload->display_errors());

            $this->load->view('upload_form', $error);
        } else {
            $data = $this->upload->data();

            $this->load->model('AES_Encryption', 'AES');

            $key = 'dit is een test';
            $this->AES->encrypt($data['full_path'], $key);
        }
    }
}