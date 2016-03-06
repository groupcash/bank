<?php
namespace groupcash\bank\app\crypto;

use groupcash\bank\app\Cryptography;

class FakeCryptography implements Cryptography {

    public function encrypt($message, $key) {
        return "$message encrypted with $key";
    }

    public function decrypt($encrypted, $key) {
        return str_replace(" encrypted with $key", '', $encrypted);
    }
}