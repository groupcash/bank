<?php
namespace groupcash\bank\app;

class Time {

    /** @var \DateTimeImmutable */
    public static $frozen;

    /**
     * @return \DateTimeImmutable
     */
    public static function now() {
        return self::$frozen ?: new \DateTimeImmutable();
    }
}