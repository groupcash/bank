<?php
namespace groupcash\bank\web;

use groupcash\bank\model\Authentication;
use rtens\domin\parameters\File;

class WebAuthentication extends Authentication {

    /**
     * @param string|File $key
     * @param null|string $password
     */
    public function __construct($key, $password) {
        parent::__construct($key, $password);
    }

    /**
     * @return string|File
     */
    public function getKey() {
        return parent::getKey();
    }
}