<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../database/Connect.php';

use \Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../', '.env');
$dotenv->load();

//

include "./views/admin.php";