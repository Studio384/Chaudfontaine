<?php

/**
 * Created by PhpStorm.
 * User: 11400277
 * Date: 13/04/2017
 * Time: 13:59
 */
class File_Encryption extends CI_Model
{
    /**
     * Database table name
     * @var string
     */
    private $table = 'file_exchange';

    public function encrypt($file, $my_private_key, $destination_users)
    {
        // Load encryption models
        $this->load->model('AES_Encryption', 'AES');
        $this->load->model('RSA_Encryption', 'RSA');

        // Generate key
        $AES_key = random_bytes(25);

        // Generate file md5 hash
        $hash = hash_file('md5', $file, true);

        // Encrypt file with AES
        // This deletes the original file
        if (!$this->AES->encrypt($file, $AES_key))
            return EXIT_ERROR;

        // Encrypt md5 hash with origin private key
        if (($enc_hash = $this->RSA->encrypt($hash, $my_private_key, true)) == EXIT_ERROR)
            return EXIT_ERROR;

        // Encrypt key with destination public key
        foreach ($destination_users as $user) {
            // Get public key from database
            if ($destination_public_key = $this->get_user_public_key($user)) {
                // Encrypt
                if (($key = $this->RSA->encrypt($AES_key, $destination_public_key, false)) == EXIT_ERROR)
                    return EXIT_ERROR;

                // Save record to database
                $dataDB = array(
                    'file_name' => $file,
                    'origin_user_id' => '1',
                    'destination_user_id' => $user,
                    'enc_key' => $key,
                    'file_hash' => $enc_hash,
                    'upload_date' => date('Y-m-d H:i:s')
                );

                $this->db->insert($this->table, $dataDB);
            } else {
                return EXIT_USER_INPUT;
            }
        }

        return EXIT_SUCCESS;
    }

    public function decrypt($file_id, $my_private_key)
    {
        // Get file information from database
        $this->db->where('id', $file_id);
        $query = $this->db->get($this->table);

        // Check if record exists
        if ($query->num_rows()) {
            $file = $query->row_array();

            // Load encryption models
            $this->load->model('AES_Encryption', 'AES');
            $this->load->model('RSA_Encryption', 'RSA');

            // Decrypt AES key
            if (($AES_key = $this->RSA->decrypt($file['enc_key'], $my_private_key, true)) == EXIT_ERROR)
                return EXIT_ERROR;

            // Decrypt file
            if (!$this->AES->decrypt($file['file_name'], $AES_key))
                return EXIT_ERROR;

            if(($this->fileCheck($file_id, $file['file_hash']) == EXIT_ERROR))
                return EXIT_ERROR;

            return EXIT_SUCCESS;
        } else {
            return EXIT_USER_INPUT;
        }
    }

    public function fileCheck($file_id, $my_hash)
    {
        // Get file information from database
        $this->db->where('id', $file_id);
        $query = $this->db->get($this->table);

        // Check if record exists
        if ($query->num_rows()) {
            $file = $query->row_array();

            if ($origin_public_key = $this->get_user_public_key($file['origin_user_id'])) {
                // Load encryption models
                $this->load->model('RSA_Encryption', 'RSA');

                // Decrypt file hash
                if (($file_hash = $this->RSA->decrypt($file['file_hash'], $origin_public_key, false)) == EXIT_ERROR)
                    return EXIT_ERROR;

                // Check if hash is the same
                if($file_hash == hex2bin($my_hash))
                    return EXIT_SUCCESS;
                else
                    return EXIT_ERROR;
            } else {
                return EXIT_ERROR;
            }
        } else {
            return EXIT_USER_INPUT;
        }
    }

    private function get_user_public_key($id)
    {
        $db = 'users';

        $this->db->where('id', $id);
        $query = $this->db->get($db);

        // Check if record exists
        if ($query->num_rows()) {
            $row = $query->row_array();
            return $row['public_key'];
        } else {
            return false;
        }
    }
}