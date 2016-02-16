<?php
namespace spec\groupcash\bank\fakes;

use groupcash\php\KeyService;

class FakeKeyService implements KeyService {

    public $nextSign;
    public $nextKey;

    /**
     * @return string
     */
    public function generatePrivateKey() {
        $key = $this->nextKey ?: 'key';
        $this->nextKey = null;
        return "private $key";
    }

    /**
     * @param string $privateKey
     * @return string
     */
    public function publicKey($privateKey) {
        return str_replace("private ", "", $privateKey);
    }

    /**
     * @param string $content
     * @param string $privateKey
     * @return string
     */
    public function sign($content, $privateKey) {
        if ($this->nextSign) {
            $sign = $this->nextSign;
            $this->nextSign = null;
            return $sign;
        }
        $content = md5($content);
        return "$content signed with $privateKey";
    }

    /**
     * @param string $content
     * @param string $signed
     * @param string $publicKey
     * @return boolean
     */
    public function verify($content, $signed, $publicKey) {
        return str_replace(" signed with private $publicKey", "", $signed) == md5($content);
    }

    /**
     * @param string $content
     * @return string
     */
    public function hash($content) {
        return "($content)";
    }
}