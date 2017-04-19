<?php

class AuthModel extends CI_Model {
    // Database name
    private $table = 'users';

    // Array with userdata if successful
    private $userdata = array();

    /**
     * User login
     * @param $email
     * @param $password
     * @return int
     */
    public function login($email, $password)
    {
        $this->db->where('email', $email);
        $query = $this->db->get($this->table);

        // Check for username in database
        if ($query->num_rows()) {
            $row = $query->row_array();

            if ($row['blocked'] == 0) {
                if (password_verify($password, $row['password'])) {

                    // Check if a newer hashing algorithm is available
                    // or the cost has changed
                    if (password_needs_rehash($row['password'], PASSWORD_DEFAULT)) {
                        // If so, create a new hash, and replace the old one
                        $newHash = password_hash($password, PASSWORD_DEFAULT);

                        $this->db->set('password', $newHash);
                        $this->db->where('user_id', $row['id']);
                        $this->db->update($this->table);
                    }

                    $access_token = 'app/' . $this->generate_random_string();
                    $this->db->set('access_token', $access_token);
                    $this->db->where('id', $row['id']);
                    $this->db->update($this->table);

                    unset($row['password']);
                    $row['access_token'] = $access_token;
                    $this->userdata = $row;
                    return EXIT_SUCCESS;
                }
                // invalid password
                return EXIT_ERROR;
            } else {
                return EXIT_ERROR_ACCOUNT_BLOCKED;
            }
        } else {
            return EXIT_ERROR;
        }
    }

    /**
     * Registers the user
     * Uses private generate_random_string
     * @param $email
     * @param $given_name
     * @param $family_name
     * @return int
     */
    public function register($email, $given_name, $family_name)
    {
        $code = $this->generate_random_string();

        // Database array
        $dataDB = array(
            'first_name' => $given_name,
            'last_name' => $family_name,
            'email' => $email,
            'public_key' => '',
            'activation_code' => $code,
            'role' => 'user',
            'blocked' => true,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        );

        $dataEmail = array(
            'code' => $code
        );

        $config = Array(
            'protocol' => 'smtp',
            'smtp_host' => 'send.one.com',
            'smtp_port' => 465,
            'smtp_user' => 'noreply@chaudfontaine.be',
            'smtp_pass' => 'o3ZhsgNlAX',
            'mailtype'  => 'html',
            'charset'   => 'iso-8859-1'
        );
        $this->load->library('email', $config);

        $this->email->from('noreply@chaudfontaine.be', 'noreply chaudfontaine.be');
        $this->email->to($email);
        $this->email->subject('Register');
        $this->email->set_mailtype("html");
        $this->email->message($this->load->view('email/activation', $dataEmail, TRUE));

        if ($this->email->send()) {
            //$this->db->insert($this->table, $dataDB);
            return EXIT_SUCCESS;
        } else {
            echo '<pre>';
            print_r($this->email->print_debugger());
            echo '</pre>';
            exit;
            return EXIT_ERROR;
        }
    }

    /**
     * Activates the account
     * @param $code
     * @param $new_password
     * @return int
     */
    public function activate($code, $new_password)
    {
        $this->db->where('activation_code', $code);
        $query = $this->db->get($this->table);

        if ($query->num_rows()) {
            $row = $query->row_array();
            $new_password = password_hash($new_password, PASSWORD_DEFAULT, ['cost' => $this->cost]);

            $access_token = $this->generate_random_string();

            $this->db->set('password', $new_password);
            $this->db->set('activation_code', '');
            $this->db->set('access_token', $access_token);
            $this->db->set('updated_at', date('Y-m-d H:i:s'));
            $this->db->set('blocked', false);
            $this->db->where('id', $row['id']);
            $this->db->update($this->table);

            unset($row['password']);
            $row['access_token'] = $access_token;
            $this->userdata = $row;

            return EXIT_SUCCESS;
        } else {
            return EXIT_ERROR;
        }
    }

    /**
     * Returns the userdata
     * @return array
     */
    public function get_data()
    {
        return $this->userdata;
    }

    /**
     * Get user access_token from database to check if he is logged in
     * @return mixed
     */
    public function get_access_token()
    {
        $this->db->where('email', $this->session->userdata('email'));
        return $this->db->get($this->table)->first_row('array')['access_token'];
    }

    /**
     * User logout
     */
    public function logout()
    {
        $this->session->unset_userdata('access_token');

        $this->db->set('access_token', '');
        $this->db->where('id', $this->session->userdata('id'));
        $this->db->update($this->table);
    }

    /**
     * Generates a random string for the activation code and reset password
     * @return string
     */
    private function generate_random_string()
    {
        $code = bin2hex(random_bytes(25));

        $this->db->where('activation_code', $code);
        $query = $this->db->get($this->table);
        
        while ($query->num_rows()) {
            $code = bin2hex(random_bytes(25));
        }

        return $code;
    }
}