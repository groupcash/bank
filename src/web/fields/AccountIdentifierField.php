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
     * @throws \Exception
     */
    public function inflate(Parameter $parameter, $serialized) {
        if (is_string($serialized)) {
            return new AccountIdentifier($serialized);
        }

        $inflated = parent::inflate($this->getParameter($parameter), $serialized);

        if ($inflated instanceof File) {
            return new AccountIdentifier($inflated->getContent());

        } else if ($inflated instanceof QrCode) {
            $matches = [];
            if (!preg_match('/target=(.*)$/', $inflated->getContent(), $matches)) {
                throw new \Exception('Could not find address in code.');
            }
            return new AccountIdentifier($matches[1]);

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
            new ClassType(QrCode::class),
            new StringType()
        ]));
    }
}