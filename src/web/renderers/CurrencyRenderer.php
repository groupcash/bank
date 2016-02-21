<?php
namespace groupcash\bank\web\renderers;

use groupcash\bank\projecting\Currency;
use rtens\domin\delivery\Renderer;
use rtens\domin\delivery\web\Element;

class CurrencyRenderer implements Renderer {

    /**
     * @param mixed $value
     * @return bool
     */
    public function handles($value) {
        return $value instanceof Currency;
    }

    /**
     * @param Currency $value
     * @return mixed
     */
    public function render($value) {
        return new Element('span', ['title' => $value->getAddress()], [$value->getName()]);
    }
}