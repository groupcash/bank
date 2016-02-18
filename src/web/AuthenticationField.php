<?php
namespace groupcash\bank\web;

use groupcash\bank\model\Authentication;
use rtens\domin\delivery\FieldRegistry;
use rtens\domin\delivery\web\fields\ObjectField;
use rtens\domin\Parameter;
use rtens\domin\parameters\File;
use watoki\reflect\type\ClassType;
use watoki\reflect\TypeFactory;

class AuthenticationField extends ObjectField {

    /** @var Authentication */
    private $sessionAuthentication;

    public function __construct(TypeFactory $types, FieldRegistry $fields, Authentication $sessionAuthentication = null) {
        parent::__construct($types, $fields);
        $this->sessionAuthentication = $sessionAuthentication;
    }

    public function handles(Parameter $parameter) {
        return $parameter->getType() == new ClassType(Authentication::class);
    }

    /**
     * @param Parameter $parameter
     * @param mixed|Authentication $authentication
     * @return string
     */
    public function render(Parameter $parameter, $authentication) {
        if (!$authentication) {
            $authentication = $this->sessionAuthentication;
        }

        if ($authentication) {
            $authentication = new WebAuthentication($authentication->getKey(), $authentication->getPassword());
        }

        return parent::render($this->getParameter($parameter), $authentication);
    }

    public function inflate(Parameter $parameter, $serialized) {
        /** @var WebAuthentication $authentication */
        $authentication = parent::inflate($this->getParameter($parameter), $serialized);

        $key = $authentication->getKey();
        if ($key instanceof File) {
            $key = $key->getContent();
        }
        return new Authentication($key, $authentication->getPassword());
    }


    /**
     * @param Parameter $parameter
     * @return Parameter
     */
    private function getParameter(Parameter $parameter) {
        return $parameter->withType(new ClassType(WebAuthentication::class));
    }
}