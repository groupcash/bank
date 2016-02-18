<?php
namespace groupcash\bank\model;

interface RandomNumberGenerator {

    /**
     * @return string
     */
    public function generate();
}