<?php
namespace groupcash\bank\web\fields;

use groupcash\bank\app\Application;
use groupcash\bank\ListCurrencies;
use groupcash\bank\model\AccountIdentifier;
use groupcash\bank\model\CurrencyIdentifier;
use groupcash\bank\projecting\AllCurrencies;
use rtens\domin\delivery\FieldRegistry;
use rtens\domin\delivery\web\fields\MultiField;
use rtens\domin\Parameter;
use rtens\domin\reflection\types\EnumerationType;
use watoki\reflect\type\ClassType;
use watoki\reflect\type\MultiType;
use watoki\reflect\type\StringType;

class CurrencyIdentifierField extends MultiField {

    /** @var string[] */
    private $currencies;

    /** @var Application */
    private $app;

    public function __construct(FieldRegistry $fields, Application $app) {
        parent::__construct($fields);
        $this->app = $app;
    }

    private function currencies() {
        if (!$this->currencies) {
            /** @var AllCurrencies $allCurrencies */
            $allCurrencies = $this->app->handle(new ListCurrencies());

            foreach ($allCurrencies->getCurrencies() as $currency) {
                $this->currencies[(string)$currency->getAddress()] = $currency->getName();
            }
        }
        return $this->currencies;
    }

    public function handles(Parameter $parameter) {
        return $parameter->getType() == new ClassType(CurrencyIdentifier::class);
    }

    public function render(Parameter $parameter, $value) {
        if ($value) {
            $value = (string)$value;
            if (!array_key_exists($value, $this->currencies())) {
                $value = new AccountIdentifier($value);
            }
        }
        return parent::render($this->getParameter($parameter), $value);
    }

    public function inflate(Parameter $parameter, $serialized) {
        $serialized = parent::inflate($this->getParameter($parameter), $serialized);
        return new CurrencyIdentifier((string)$serialized);
    }

    public function headElements(Parameter $parameter) {
        return parent::headElements($this->getParameter($parameter));
    }

    /**
     * @param Parameter $parameter
     * @return Parameter
     */
    private function getParameter(Parameter $parameter) {
        return $parameter->withType(new MultiType([
            new EnumerationType($this->currencies(), new StringType()),
            new ClassType(AccountIdentifier::class)
        ]));
    }
}