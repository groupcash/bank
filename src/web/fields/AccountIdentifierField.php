<?php
namespace groupcash\bank\web\fields;

use groupcash\bank\model\AccountIdentifier;
use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\HeadElements;
use rtens\domin\delivery\web\WebField;
use rtens\domin\Parameter;
use watoki\reflect\type\ClassType;

class AccountIdentifierField implements WebField {

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
        return new AccountIdentifier($serialized);
    }

    /**
     * @param Parameter $parameter
     * @param mixed $value
     * @return string
     */
    public function render(Parameter $parameter, $value) {
        $id = 'qr-reader-' . str_replace(['[', ']'], '-', $parameter->getName());

        return (string)new Element('div', [], [
            new Element('input', array_merge([
                'class' => 'form-control',
                'type' => 'text',
                'name' => $parameter->getName(),
                'value' => $value,
                'id' => "$id-value"
            ], $parameter->isRequired() ? [
                'required' => 'required'
            ] : [])),
            new Element('div', [
                'class' => 'btn btn-success',
                'onclick' => "scan_qr_code('#$id');"
            ], ['Scan QR code']),
            new Element('div', [
                'id' => $id,
                'style' => 'width:300px; height:300px; display: none;'
            ])
        ]);
    }

    /**
     * @param Parameter $parameter
     * @return array|Element[]
     */
    public function headElements(Parameter $parameter) {
        return [
            HeadElements::jquery(),
            HeadElements::script('https://dwa012.github.io/html5-qrcode/javascripts/html5-qrcode.min.js'),
            new Element('script', [], [
                'function scan_qr_code(id) {
                    $(id).show();
                    $(id).html5_qrcode(
                        function(data) {
                            var match = /target=(.*)$/g.exec(data);
                            if (!match) {
                                $(id + "-value").val("Invalid content");
                            } else {
                                $(id + "-value").val(match[1]);
                            }
                            $(id).empty();
                            $(id).hide();
                        },
                        function(){},
                        function (){}
                    );
                }'
            ])
        ];
    }
}