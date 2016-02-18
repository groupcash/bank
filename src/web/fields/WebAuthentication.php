<?php
namespace groupcash\bank\web\fields;

use groupcash\bank\model\Authentication;
use rtens\domin\parameters\File;

class WebAuthentication extends Authentication {

    /**
     * @param File|string $key
     * @param null|string $password
     */
    public function __construct($key, $password) {
        parent::__construct($key, $password);
    }

    /**
     * @return File|string
     */
    public function getKey() {
        return parent::getKey();
    }
}