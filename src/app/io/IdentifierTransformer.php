<?php
namespace groupcash\bank\app\io;

use groupcash\bank\model\Identifier;
use watoki\stores\transforming\transformers\ObjectTransformer;

class IdentifierTransformer extends ObjectTransformer {

    public function canTransform($value) {
        return parent::canTransform($value) && $value instanceof Identifier;
    }

    public function hasTransformed($transformed) {
        return
            parent::hasTransformed($transformed)
            && is_subclass_of($this->mapper->getClass($transformed[self::TYPE_KEY]), Identifier::class);
    }

    /**
     * @param Identifier $object
     * @return mixed
     */
    protected function transformObject($object) {
        return $object->getIdentifier();
    }

    /**
     * @param mixed $transformed
     * @param string $type
     * @return object
     */
    protected function revertObject($transformed, $type) {
        return new $type($transformed);
    }
}