<?php

require 'vendor/autoload.php';

use AbdelrhmanSaeed\Route\API\Route;
use Symfony\Component\HttpFoundation\{Request, Response};

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Initialize the routing system
Route::setup('src/Routes', Request::createFromGlobals(), new Response);