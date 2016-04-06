<?php
$_ENV['base_path'] = $abspath;
$_ENV['framework_path'] = __DIR__;

$loader = require $_ENV['base_path'] . '/vendor/autoload.php';

// Load .env file
$dotenv = new \Dotenv\Dotenv($_ENV['base_path']);
$dotenv->load();

require_once 'helpers.php';

$_ENV['app_name'] = env('APP_NAME');
$loader->addPsr4($_ENV['app_name'] . '\\', $_ENV['base_path'] . 'app/');