<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Created by PhpStorm.
 * User: 11400277
 * Date: 20/04/2017
 * Time: 12:40
 */
class RestController extends MY_Controller
{
    public function get_users()
    {
        $input = trim($this->input->get('input', TRUE));
        $email = trim($this->input->get('term', TRUE));

        $input = explode(",", $input);

        $this->db->select('email');
        $this->db->from('users');
        $this->db->like('email', $email);
        $this->db->limit('5');
        $query = $this->db->get();
        $data = null;

        foreach ($query->result() as $row) {
            $taken = false;

            foreach ($input as $user) {
                if (trim($user) == $row->email)
                    $taken = true;
            }

            if (!$taken)
                $data[] = $row->email;
        }

        echo json_encode($data);
    }
}