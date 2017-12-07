<?php

require __DIR__ . '/../src/autoload.php';
$cfg = [
    'adapter' => 'Http',
    'debug' => true,
];
(new \lingyin\profile\LogSync())->handler($cfg)->run();
