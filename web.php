<?php

require_once __DIR__ . '/vendor/autoload.php';

$context = (new \watoki\curir\WebEnvironment($_SERVER, [], []))->getContext();
(new \groupcash\bank\web\Launcher(__DIR__, (string)$context))->run();