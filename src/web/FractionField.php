<?php
namespace groupcash\bank\web;

use groupcash\php\model\Fraction;
use rtens\domin\delivery\web\fields\StringField;
use rtens\domin\Parameter;
use watoki\reflect\type\ClassType;

class FractionField extends StringField {

    const PRECISION = 8;

    public function handles(Parameter $parameter) {
        return $parameter->getType() == new ClassType(Fraction::class);
    }

    /**
     * @param Parameter $parameter
     * @param Fraction $value
     * @return string
     */
    public function render(Parameter $parameter, $value) {
        $float = $value;
        if ($float) {
            $float = round($value->toFloat(), self::PRECISION);
            if ($float != $value->toFloat()) {
                $float = (string)$value;
            }
        }
        return parent::render($parameter, $float);
    }

    public function inflate(Parameter $parameter, $serialized) {
        if (strpos($serialized, '/')) {
            list($nom, $den) = explode('/', $serialized);
            $fraction = new Fraction(intval($nom), intval($den));
        } else if (floatval($serialized)) {
            $float = floatval($serialized);
            $den = 1;
            while ($float * $den != (int)($float * $den)) {
                $den *= 10;
            }
            $fraction = new Fraction($float * $den, $den);
        } else {
            $fraction = new Fraction(intval($serialized));
        }

        return $fraction;
    }
}