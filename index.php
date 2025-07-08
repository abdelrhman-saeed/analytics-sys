<?php

require 'vendor/autoload.php';

use Dotenv\Dotenv;
use AbdelrhmanSaeed\Route\API\Route;
use Symfony\Component\HttpFoundation\{Request, Response};


Dotenv::createImmutable(__DIR__)->load();


// Initialize the routing system
Route::setup('src/Routes', Request::createFromGlobals(), new Response);