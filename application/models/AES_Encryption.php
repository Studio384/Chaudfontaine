<?php
define('FILE_ENCRYPTION_BLOCKS', 10000);

/**
 * Created by PhpStorm.
 * User: 11400277
 * Date: 18/03/2017
 * Time: 15:17
 */
class AES_Encryption extends CI_Model
{
    public function encrypt($source, $key)
    {
        $dest = $source . '.aes';
        $key = substr(sha1($key, true), 0, 16);
        $iv = openssl_random_pseudo_bytes(16);

        $error = false;
        if ($fpOut = fopen($dest, 'w')) {
            // Put the initialzation vector to the beginning of the file
            fwrite($fpOut, $iv);
            if ($fpIn = fopen($source, 'rb')) {
                while (!feof($fpIn)) {
                    $plaintext = fread($fpIn, 16 * FILE_ENCRYPTION_BLOCKS);
                    $ciphertext = openssl_encrypt($plaintext, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
                    // Use the first 16 bytes of the ciphertext as the next initialization vector
                    $iv = substr($ciphertext, 0, 16);
                    fwrite($fpOut, $ciphertext);
                }
                fclose($fpIn);
            } else {
                $error = true;
            }
            fclose($fpOut);
        } else {
            $error = true;
        }

        if (!unlink($source))
            $error = true;

        return $error ? false : true;
    }

    public function decrypt($source, $key)
    {
        $dest = $source;
        $source = $source . '.aes';
        $key = substr(sha1($key, true), 0, 16);

        $error = false;
        if ($fpOut = fopen($dest, 'w')) {
            if ($fpIn = fopen($source, 'rb')) {
                // Get the initialzation vector from the beginning of the file
                $iv = fread($fpIn, 16);
                while (!feof($fpIn)) {
                    $ciphertext = fread($fpIn, 16 * (FILE_ENCRYPTION_BLOCKS + 1)); // we have to read one block more for decrypting than for encrypting
                    $plaintext = openssl_decrypt($ciphertext, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
                    // Use the first 16 bytes of the ciphertext as the next initialization vector
                    $iv = substr($ciphertext, 0, 16);
                    fwrite($fpOut, $plaintext);
                }
                fclose($fpIn);
            } else {
                $error = true;
            }
            fclose($fpOut);
        } else {
            $error = true;
        }

        // Save content and delete non encrypted file
        $file_content = file_get_contents($dest); // Read the file's contents

        if (!unlink($dest))
            $error = true;

        if (!$error) {
            // Get file extension
            $pieces = explode(".", $dest);
            array_shift($pieces);
            $extension = null;
            foreach ($pieces as $piece) {
                $extension = $extension . '.' . $piece;
            }

            // Download file
            force_download('secure_download' . $extension, $file_content);
        }

        return $error ? false : true;
    }
}