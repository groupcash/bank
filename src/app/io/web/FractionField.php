<?php
namespace groupcash\bank\app\io\web;

use groupcash\php\io\FractionParser;
use groupcash\php\model\value\Fraction;
use rtens\domin\delivery\web\fields\StringField;
use rtens\domin\Parameter;
use watoki\reflect\type\ClassType;

class FractionField extends StringField {

    public function handles(Parameter $parameter) {
        return $parameter->getType() == new ClassType(Fraction::class);
    }

    public function inflate(Parameter $parameter, $serialized) {
        return (new FractionParser())->parse(parent::inflate($parameter, $serialized));
    }
    /**

     * @param Parameter $parameter
     * @param Fraction $value
     * @return string
     */
    public function render(Parameter $parameter, $value) {
        if (!$value) {
            return parent::render($parameter, $value);
        }

        $float = round($value->toFloat(), 10);
        return parent::render($parameter, $float == $value->toFloat() ? $float : (string)$value);
    }
}