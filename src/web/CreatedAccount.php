<?php
namespace groupcash\bank\web;

use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\renderers\dashboard\types\Panel;

class CreatedAccount {

    public static function render($sendCoinsUrl, $keys) {
        return [
            self::keyPanel('Private Key', $keys['key']),
            self::keyPanel('Public Address', $keys['address']),
            new Panel('Send Coins', [
                new Element('p', [], [
                    'You can use this QR code to send coins to the created account.'
                ]),
                new Element('img', [
                    'src' => 'https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=' .
                        urlencode($sendCoinsUrl . '?target='. $keys['address'])
                ])
            ])
        ];
    }

    static private function keyPanel($heading, $content) {
        return new Panel($heading, [
            new Element('textarea', [
                'class' => 'form-control',
                'onclick' => 'this.select();'
            ], [
                $content
            ]),
            new Element('a', [
                'class' => 'btn btn-success',
                'download' => str_replace(' ', '_', strtolower($heading)) . '_' . substr(md5($content), -6),
                'href' => 'data:text/plain;base64,' . base64_encode($content),
                'target' => '_blank'
            ], [
                'Save as File'
            ])
        ]);
    }
}