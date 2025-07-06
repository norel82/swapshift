<?php
$envFile = __DIR__ . '/../.env';

if (!file_exists($envFile)) {
    throw new Exception('.env file not found');
}

$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    if (strpos(trim($line), '#') === 0) continue;
    putenv($line);
}
