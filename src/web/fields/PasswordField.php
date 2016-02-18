<?php
namespace groupcash\bank\web\fields;

use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\fields\StringField;
use rtens\domin\Parameter;
use watoki\reflect\type\StringType;

class PasswordField extends StringField {

    /**
     * @param Parameter $parameter
     * @return bool
     */
    public function handles(Parameter $parameter) {
        return $parameter->getType() == new StringType() && strpos($parameter->getName(), 'password') !== false;
    }

    /**
     * @param Parameter $parameter
     * @param mixed $value
     * @return string
     */
    public function render(Parameter $parameter, $value) {
        return (string)new Element('input', array_merge([
            'class' => 'form-control',
            'type' => 'password',
            'name' => $parameter->getName(),
            'value' => $value
        ], $parameter->isRequired() ? [
            'required' => 'required'
        ] : []));
    }
}