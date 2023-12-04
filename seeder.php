<?php

// Path: seeder.php
require_once 'vendor/autoload.php';
require_once 'database.php';

// Import RedBean library
use RedBeanPHP\R as R;

// Set up a connection to the MySQL database
$user = R::findOne('users', 'username = ?', ['gadisa']);

if ($user) {
    echo "User with this username already exists!\n";
    exit;
}

$user = R::dispense('users');
$user->username = 'gadisa';
$user->password = password_hash('123', PASSWORD_DEFAULT);
R::store($user);

echo "User seeded!\n";