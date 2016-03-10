<?php

require_once __DIR__ . '/vendor/autoload.php';

$context = (new \watoki\curir\WebEnvironment($_SERVER, [], []))->getContext();
(new \groupcash\bank\app\web\Launcher(__DIR__ . '/user', (string)$context))->run();