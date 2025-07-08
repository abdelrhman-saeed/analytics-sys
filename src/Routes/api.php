<?php

use AbdelrhmanSaeed\Route\Api\Route;
use AnalyticsSystem\Controllers\{
    ProductController, OrderController, AnalyticsController, RecommendationsController
};


/**
 * products
 */
Route::resource('products', ProductController::class);

/**
 * orders
 */
Route::post('orders', [OrderController::class, 'save']);

/**
 * analytics
 */
Route::get('analytics', [AnalyticsController::class, 'index']);

/**
 * recommendations
 */
Route::get('recommendations', [RecommendationsController::class, 'index']);