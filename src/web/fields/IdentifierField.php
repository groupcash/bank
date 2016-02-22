<?php
namespace groupcash\bank\web\fields;

use groupcash\bank\model\Identifier;
use rtens\domin\delivery\web\fields\StringField;
use rtens\domin\Parameter;
use watoki\reflect\type\ClassType;

class IdentifierField extends StringField {

    public function handles(Parameter $parameter) {
        $type = $parameter->getType();
        return $type instanceof ClassType && is_subclass_of($type->getClass(), Identifier::class);
    }

    /**
     * @param Parameter $parameter
     * @param Identifier $value
     * @return string
     */
    public function render(Parameter $parameter, $value) {
        return parent::render($parameter, (string)$value);
    }

    public function inflate(Parameter $parameter, $serialized) {
        /** @var ClassType $type */
        $type = $parameter->getType();
        $class = $type->getClass();

        return new $class($serialized);
    }
}