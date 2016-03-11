<?php
namespace groupcash\bank\app\io;

use groupcash\php\model\signing\Binary;
use watoki\stores\transforming\transformers\ClassTransformer;

class BinaryTransformer extends ClassTransformer {

    /**
     * @return string
     */
    protected function getClass() {
        return Binary::class;
    }

    /**
     * @param Binary $object
     * @return mixed
     */
    protected function transformObject($object) {
        return base64_encode($object->getData());
    }

    /**
     * @param mixed $transformed
     * @param string $type
     * @return object
     */
    protected function revertObject($transformed, $type) {
        return new Binary(base64_decode($transformed));
    }
}