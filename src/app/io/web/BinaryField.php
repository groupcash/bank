<?php
namespace groupcash\bank\app\io\web;

use groupcash\php\model\signing\Binary;
use rtens\domin\delivery\web\fields\StringField;
use rtens\domin\Parameter;
use watoki\reflect\type\ClassType;

class BinaryField extends StringField {

    public function handles(Parameter $parameter) {
        return $parameter->getType() == new ClassType(Binary::class);
    }

    public function inflate(Parameter $parameter, $serialized) {
        return new Binary(base64_decode(parent::inflate($parameter, $serialized)));
    }

    /**
     * @param Parameter $parameter
     * @param Binary $value
     * @return string
     */
    public function render(Parameter $parameter, $value) {
        return parent::render($parameter, $value ? base64_encode($value->getData()) : null);
    }

}