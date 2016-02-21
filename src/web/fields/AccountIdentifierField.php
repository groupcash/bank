<?php
namespace groupcash\bank\web\fields;

use groupcash\bank\model\AccountIdentifier;
use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\fields\MultiField;
use rtens\domin\Parameter;
use rtens\domin\parameters\File;
use watoki\reflect\type\ClassType;
use watoki\reflect\type\MultiType;
use watoki\reflect\type\StringType;

class AccountIdentifierField extends MultiField {

    /**
     * @param Parameter $parameter
     * @return bool
     */
    public function handles(Parameter $parameter) {
        return $parameter->getType() == new ClassType(AccountIdentifier::class);
    }

    /**
     * @param Parameter $parameter
     * @param string $serialized
     * @return mixed
     */
    public function inflate(Parameter $parameter, $serialized) {
        $inflated = parent::inflate($this->getParameter($parameter), $serialized);
        if ($inflated instanceof File) {
            return new AccountIdentifier($inflated->getContent());
        } else if ($inflated instanceof AccountIdentifier) {
            return $inflated;
        } else {
            return new AccountIdentifier($inflated);
        }
    }

    /**
     * @param Parameter $parameter
     * @param mixed $value
     * @return string
     */
    public function render(Parameter $parameter, $value) {
        if ($value) {
            $value = (string)$value;
        }
        return parent::render($this->getParameter($parameter), $value);
    }

    /**
     * @param Parameter $parameter
     * @return array|Element[]
     */
    public function headElements(Parameter $parameter) {
        return parent::headElements($this->getParameter($parameter));
    }

    /**
     * @param Parameter $parameter
     * @return Parameter
     */
    private function getParameter(Parameter $parameter) {
        return $parameter->withType(new MultiType([
            new ClassType(File::class),
            new ClassType(AddressCode::class),
            new StringType()
        ]));
    }
}