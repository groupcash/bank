<?php
namespace groupcash\bank\web\fields;

use groupcash\bank\app\Application;
use groupcash\bank\ListBackers;
use groupcash\bank\model\BackerIdentifier;
use groupcash\bank\projecting\AllBackers;
use rtens\domin\delivery\FieldRegistry;
use rtens\domin\delivery\web\fields\EnumerationField;
use rtens\domin\Parameter;
use rtens\domin\reflection\types\EnumerationType;
use watoki\reflect\type\ClassType;
use watoki\reflect\type\StringType;

class BackerField extends EnumerationField {

    /** @var string[] names indexed by address */
    private $backers;

    public function __construct(FieldRegistry $fields, Application $app) {
        parent::__construct($fields);

        /** @var AllBackers $backers */
        $backers = $app->handle(new ListBackers());
        foreach ($backers->getBackers() as $backer) {
            $this->backers[(string)$backer->getAddress()] = $backer->getCurrencyName() . ' - ' . $backer->getName();
        }
    }

    public function handles(Parameter $parameter) {
        return $parameter->getType() == new ClassType(BackerIdentifier::class);
    }

    public function inflate(Parameter $parameter, $serialized) {
        $address = parent::inflate($this->getParameter($parameter), $serialized);
        return new BackerIdentifier($address);
    }

    public function render(Parameter $parameter, $value) {
        return parent::render($this->getParameter($parameter), (string)$value);
    }

    public function headElements(Parameter $parameter) {
        return parent::headElements($this->getParameter($parameter));
    }

    /**
     * @param Parameter $parameter
     * @return Parameter
     */
    private function getParameter(Parameter $parameter) {
        return $parameter->withType(new EnumerationType($this->backers, new StringType()));
    }
}