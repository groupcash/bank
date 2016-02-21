<?php
namespace groupcash\bank\web\renderers;

use groupcash\php\cli\CoinSerializer;
use groupcash\php\cli\SerializingRenderer;
use groupcash\php\model\Coin;
use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\WebRenderer;

class CoinsRenderer implements WebRenderer {

    /** @var SerializingRenderer */
    private $serializer;

    /**
     * CoinsRenderer constructor.
     */
    public function __construct() {
        $this->serializer = new SerializingRenderer([new CoinSerializer()]);
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function handles($value) {
        return is_array($value) && !empty($value) && $value[0] instanceof Coin;
    }

    /**
     * @param Coin[] $value
     * @return mixed
     */
    public function render($value) {
        $coins = implode("\n\n", array_map(function (Coin $coin) {
            return $this->serializer->render($coin);
        }, $value));

        return new Element('div', [], [
            new Element('textarea', [
                'class' => 'form-control',
                'onclick' => 'this.select();',
                'rows' => 15
            ], [
                $coins
            ]),
            new Element('a', [
                'class' => 'btn btn-success',
                'download' => 'withdrawn_coins_' . date('YmdHis'),
                'href' => 'data:text/plain;base64,' . base64_encode($coins),
                'target' => '_blank'
            ], [
                'Save as File'
            ])
        ]);
    }

    /**
     * @param mixed $value
     * @return array|Element[]
     */
    public function headElements($value) {
        return [];
    }
}