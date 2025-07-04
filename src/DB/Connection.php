<?php

namespace AnalyticsSystem\DB;


class Connection {

    public \PDO $pdo;

    public function __construct()
    {
        $this->pdo = new \PDO('sqlite:' . __DIR__ . '/database.sqlite');
        $this->pdo->exec("PRAGMA foreign_keys = ON");
    }
}