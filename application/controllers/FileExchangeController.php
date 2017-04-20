<?php

/**
 * Created by PhpStorm.
 * User: 11400277
 * Date: 18/03/2017
 * Time: 14:57
 */
class FileExchangeController extends MY_Controller
{
    private $dir = 'fileExchange/';

    public function index()
    {
        $this->display($this->dir . 'upload');
    }

    public function upload()
    {
        // Get ID's and return in array
        $users = array('');

        // Set private key null
        $my_private_key = null;

        // Settings for private key file upload
        $config['upload_path'] = './uploaded_files/';
        $config['allowed_types'] = 'txt';

        // Upload txt file, read it, delete it
        $this->load->library('upload', $config);
        if (!$this->upload->do_upload('privateKey')) {
            $error = array('error' => $this->upload->display_errors());
            // File error
        } else {
            $data = $this->upload->data();
            $file_path = $data['full_path'];
            $my_private_key = file_get_contents($file_path, FILE_USE_INCLUDE_PATH);

            // Delete txt file on server
            if ($this->delete($file_path) == EXIT_ERROR) {
                // Show error
            } else {
                // Show error but file is secure
            }
        }
        // Ending txt file upload

        // Begin zip upload
        // Settings for zip file upload
        $config['upload_path'] = './uploaded_files/';
        $config['allowed_types'] = 'zip';
        $config['encrypt_name'] = true;

        //$config['max_size'] = 100;
        //$config['max_width'] = 1024;
        //$config['max_height'] = 768;

        $this->load->library('upload', $config);

        // Upload and encrypt zip file
        if (!$this->upload->do_upload('file')) {
            $error = array('error' => $this->upload->display_errors());
            // File error
        } else {
            $data = $this->upload->data();
            $file_path = $data['full_path'];

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

                // 1 or more errors --> delete file on server
                if ($this->delete($file_path) == EXIT_ERROR) {
                    // Show error
                } else {
                    // Show error but file is secure
                }
            }
        }
        // Ending zip file upload
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

    public function fileCheck()
    {
        $file_id = 0;
        $my_hash = null;

        $this->load->model('File_Encryption', 'file');
        $result = $this->file->fileCheck($file_id, $my_hash);

        if ($result == EXIT_SUCCESS) {
            // show success
            echo 'success';
        } elseif ($result == EXIT_ERROR) {
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