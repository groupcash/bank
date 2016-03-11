<?php

require_once __DIR__ . '/vendor/autoload.php';

$context = (new \watoki\curir\WebEnvironment($_SERVER, [], []))->getContext();
(new \groupcash\bank\app\io\web\Launcher(__DIR__ . '/user', (string)$context))->run();