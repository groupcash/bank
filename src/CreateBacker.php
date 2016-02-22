<?php
namespace groupcash\bank;

use groupcash\bank\app\sourced\messaging\Command;

class CreateBacker implements Command {

    /** @var string */
    private $name;

    /**
     * @param string $name
     */
    public function __construct($name) {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }
}