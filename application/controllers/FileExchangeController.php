<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Created by PhpStorm.
 * User: 11400277
 * Date: 18/03/2017
 * Time: 14:57
 */
class FileExchangeController extends MY_Controller
{
    private $dir = 'fileExchange/';

    public function upload()
    {
        $this->form_validation->set_rules('users', '', 'trim|required');
        $this->form_validation->set_rules('privateKey', '', 'trim|required');

        if ($this->form_validation->run() == true) {
            // Make array of multiple emails
            $users = explode(',', $this->input->post('users'));
            unset($users[count($users) - 1]);
            $my_private_key = $this->input->post('privateKey');

            // Begin zip upload
            // Settings for zip file upload
            $config['upload_path'] = './uploaded_files/';
            $config['allowed_types'] = 'zip';

            $this->load->library('upload', $config);

            // Upload and encrypt zip file
            if (!$this->upload->do_upload('file')) {
                $data['errors'][] = $this->upload->display_errors();
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
                        $data['errors'][] = "Een user tussen de lijst bestaat niet.";
                    }

                    if ($result == EXIT_ERROR) {
                        $data['errors'][] = "Encryption failed, waarschijnlijk een verkeerde private key.";
                    }

                    // 1 or more errors --> delete file on server
                    if ($this->delete($file_path) == EXIT_ERROR) {
                        $data['errors'][] = "Probleem met file te deleten.";
                    } else {
                        $data['errors'][] = "file deleted succesfuly.";
                    }
                }
            }
            // Ending zip file upload
        }
        if (isset($data['errors'])) {
            $this->display($this->dir . 'uploadErrors', $data);
        } else {
            $this->display($this->dir . 'upload');
        }
    }

    public function download($id = null)
    {
        if (isset($id)) {
            $file_id = $id;

            $this->form_validation->set_rules('privateKey', '', 'trim|required');
            if ($this->form_validation->run() == true) {
                $my_private_key = $this->input->post('privateKey');

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
            $this->display($this->dir . 'download');
        } else {
            $this->db->where('destination_user_id', 0);
            $data['files'] = $this->db->get('file_exchange')->result_array();

            $this->display($this->dir . 'downloadList', $data);
        }
    }

    public function fileCheck($id)
    {
        $file_id = $id;
        $this->form_validation->set_rules('md5', '', 'trim|required');
        if ($this->form_validation->run() == true) {
            $my_hash = $this->input->post('md5');

            $this->load->model('File_Encryption', 'file');
            $result = $this->file->fileCheck($file_id, $my_hash);

            if ($result == EXIT_SUCCESS) {
                // show success
                echo 'success';
            } elseif ($result == EXIT_ERROR) {
                echo 'test';
            }
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