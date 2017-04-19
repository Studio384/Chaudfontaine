<?php

/**
 * Created by PhpStorm.
 * User: 11400277
 * Date: 18/03/2017
 * Time: 16:29
 */
class RSA_Encryption extends CI_Model
{
    public function generate_keys()
    {
        $config = array(
            "digest_alg" => "sha512",
            "private_key_bits" => 4096,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        );

        // Create the private and public key
        $res = openssl_pkey_new($config);

        // Extract the private key from $res to $privKey
        openssl_pkey_export($res, $privKey);

        // Extract the public key from $res to $pubKey
        $pubKey = openssl_pkey_get_details($res);
        $pubKey = $pubKey["key"];

        return array(
            'private' => $privKey,
            'public' => $pubKey
        );
    }

    /**
     * @param $data
     * @param $key
     * @param boolean $personal
     * @return mixed
     */
    public function encrypt($data, $key, $personal)
    {
        if ($personal)
            if (openssl_private_encrypt($data, $encrypted, $key))
                return $encrypted;
            else
                return EXIT_ERROR;
        else
            if (openssl_public_encrypt($data, $encrypted, $key))
                return $encrypted;
            else
                return EXIT_ERROR;
    }

    /**
     * @param $data
     * @param $key
     * @param boolean $personal
     * @return mixed
     */
    public function decrypt($data, $key, $personal)
    {
        if ($personal)
            if (openssl_private_decrypt($data, $decrypted, $key))
                return $decrypted;
            else
                return EXIT_ERROR;
        else
            if (openssl_public_decrypt($data, $decrypted, $key))
                return $decrypted;
            else
                return EXIT_ERROR;
    }
}