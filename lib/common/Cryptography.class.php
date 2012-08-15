<?php
set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__).'/vendor/phpseclib');
include('Crypt/RSA.php');
class Cryptography{
    private $publicKey;
    private $privateKey;
    private $rsa;

    public function __construct($publicKey = NULL, $privateKey = NULL){
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
        $this->rsa = new Crypt_RSA();
    }

    public function encrypt($plaintext){
        $this->rsa->loadKey($this->publicKey);
        $encrypted = base64_encode($this->rsa->encrypt($plaintext));
        return $encrypted;
    }

    public function decrypt($ciphertext){
        $this->rsa->loadKey($this->privateKey);
        $decrypted = $this->rsa->decrypt(base64_decode($ciphertext));
        return $decrypted;
    }
}
?>