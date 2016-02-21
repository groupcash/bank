<?php
namespace groupcash\bank\web\renderers;

use groupcash\bank\model\CreatedAccount;
use rtens\domin\ActionRegistry;
use rtens\domin\delivery\RendererRegistry;
use rtens\domin\delivery\web\Element;
use rtens\domin\delivery\web\renderers\dashboard\types\Panel;
use rtens\domin\delivery\web\renderers\link\LinkPrinter;
use rtens\domin\delivery\web\renderers\link\LinkRegistry;
use rtens\domin\delivery\web\renderers\ListRenderer;
use rtens\domin\delivery\web\WebCommentParser;

class CreatedAccountRenderer extends ListRenderer {

    /** @var string */
    private $sendCoinsUrl;

    /**
     * @param RendererRegistry $renderers
     * @param string $sendCoinsUrl
     */
    public function __construct(RendererRegistry $renderers, $sendCoinsUrl) {
        parent::__construct($renderers, new LinkPrinter(new LinkRegistry(), new ActionRegistry(), new WebCommentParser()));
        $this->sendCoinsUrl = $sendCoinsUrl;
    }

    /**
     * @param mixed $value
     * @return bool
     */
    public function handles($value) {
        return $value instanceof CreatedAccount;
    }

    /**
     * @param CreatedAccount $createdAccount
     * @return mixed
     */
    public function render($createdAccount) {
        $panels = [
            $this->keyPanel('Public Address', $createdAccount->getAddress()),
            $this->codePanel('Send Coins',
                'You can use this QR code to send coins to the created account.',
                $this->sendCoinsUrl . '?target=' . $createdAccount->getAddress())
        ];

        if ($createdAccount->getKey()) {
            $panels[] = $this->keyPanel('Private Key', $createdAccount->getKey());
            $panels[] = $this->codePanel('Private Code',
                'This code contains your private key. Handle with care.',
                $createdAccount->getKey());
        }

        return parent::render($panels);
    }

    private function keyPanel($heading, $content) {
        return new Panel($heading, [
            new Element('textarea', [
                'class' => 'form-control',
                'onclick' => 'this.select();'
            ], [
                $content
            ]),
            new Element('a', [
                'class' => 'btn btn-success',
                'download' => str_replace(' ', '_', strtolower($heading)) . '_' . date('YmdHis'),
                'href' => 'data:text/plain;base64,' . base64_encode($content),
                'target' => '_blank'
            ], [
                'Save as File'
            ])
        ]);
    }

    private function codePanel($heading, $description, $content) {
        return new Panel($heading, [
            new Element('p', [], [
                $description
            ]),
            new Element('img', [
                'src' => 'https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=' . urlencode($content)
            ])
        ]);
    }
}