<?php

/**
 * Created by PhpStorm.
 * User: 11400277
 * Date: 18/03/2017
 * Time: 14:57
 */
class File_Exchange extends CI_Controller
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

    public function upload()
    {
        $config['upload_path'] = './uploaded_files/';
        $config['allowed_types'] = 'zip';

        //$config['max_size'] = 100;
        //$config['max_width'] = 1024;
        //$config['max_height'] = 768;

        $config['encrypt_name'] = true;

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('userfile')) {
            $error = array('error' => $this->upload->display_errors());

            $this->load->view('upload_form', $error);
        } else {
            $data = $this->upload->data();
            $file_path = $data['full_path'];
            $my_private_key = null;
            $users = array('');

            $this->load->model('File_Encryption', 'file');
            $result = $this->file->encrypt($file_path, $my_private_key, $users);

            if ($result == EXIT_SUCCESS) {
                // show success
                echo 'success';
            } else {
                if ($result == EXIT_USER_INPUT) {
                    // Show error user does not exist
                }

                if ($result == EXIT_ERROR) {
                    // Encryption failed
                }


                if ($this->delete($file_path) == EXIT_ERROR) {
                    // Show error
                } else {
                    // Show error but file is secure
                }
            }
        }
    }

    public function download()
    {
        $file_id = 0;
        $my_private_key = null;

        $this->load->model('File_Encryption', 'file');
        $result = $this->file->decrypt($file_id, $my_private_key);

        if ($result == EXIT_SUCCESS) {
            // show success
            echo 'success';
        } elseif ($result == EXIT_USER_INPUT) {
            // File not found
        } elseif ($result == EXIT_ERROR) {
            // Decryption error (error in code or wrong keys) 90% = wrong key
        }
    }

    public function check_file()
    {
        $file_id = 0;
        $my_hash = null;

        $this->load->model('File_Encryption', 'file');
        $result = $this->file->check_file($file_id, $my_hash);

        if($result == EXIT_SUCCESS)
        {
            // show success
            echo 'success';
        }elseif($result == EXIT_ERROR)
        {
            echo 'test';
        }
    }

    private function delete($file)
    {
        if (file_exists($file)) {
            if (!unlink($file))
                return EXIT_ERROR;
        }

        if (file_exists($file . '.aes')) {
            if (!unlink($file . '.aes'))
                return EXIT_ERROR;
        }

        return EXIT_SUCCESS;
    }
}