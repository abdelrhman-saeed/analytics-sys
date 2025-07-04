<?php

namespace AnalyticsSystem\Controllers;

use AnalyticsSystem\DB\Connection as DBConnection;
use Symfony\Component\HttpFoundation\{Request, Response, JsonResponse};


abstract class BaseController {


    protected Request $request;
    protected \PDO    $pdo;

    public function __construct() {
        $this->request  = Request::createFromGlobals();
        $this->pdo      = (new DBConnection)->pdo;
    }
}