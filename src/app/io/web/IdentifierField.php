<?php
namespace groupcash\bank\app\io\web;

use groupcash\bank\model\Identifier;
use rtens\domin\delivery\web\fields\StringField;
use rtens\domin\Parameter;
use watoki\reflect\type\ClassType;

class IdentifierField extends StringField {

    public function handles(Parameter $parameter) {
        $type = $parameter->getType();
        return $type instanceof ClassType && is_subclass_of($type->getClass(), Identifier::class);
    }

    public function inflate(Parameter $parameter, $serialized) {
        /** @var ClassType $type */
        $type = $parameter->getType();
        $class = $type->getClass();
        $identifier = parent::inflate($parameter, $serialized);
        return new $class($identifier);
    }
}