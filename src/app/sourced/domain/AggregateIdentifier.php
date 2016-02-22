<?php
namespace groupcash\bank\app\sourced\domain;

interface AggregateIdentifier {

    /**
     * @return string
     */
    function __toString();
}