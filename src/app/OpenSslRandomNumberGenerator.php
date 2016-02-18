<?php
namespace groupcash\bank\app;

use groupcash\bank\model\RandomNumberGenerator;

class OpenSslRandomNumberGenerator implements RandomNumberGenerator {

    /**
     * @return string
     */
    public function generate() {
        return base64_encode(openssl_random_pseudo_bytes(128));
    }
}