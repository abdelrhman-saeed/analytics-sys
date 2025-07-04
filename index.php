<?php

require 'vendor/autoload.php';

use AbdelrhmanSaeed\Route\API\Route;
use Symfony\Component\HttpFoundation\{Request, Response};

// Initialize the routing system
Route::setup('src/Routes', Request::createFromGlobals(), new Response);