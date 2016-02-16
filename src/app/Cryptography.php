<?php
namespace groupcash\bank\app;

interface Cryptography {

    /**
     * @param string $message
     * @param string $key
     * @return string
     */
    public function encrypt($message, $key);

    /**
     * @param string $encrypted
     * @param string $key
     * @return string
     */
    public function decrypt($encrypted, $key);
}