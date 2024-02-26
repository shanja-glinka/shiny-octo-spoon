<?php

namespace app\utils;

final class Connection extends CustomPDO
{
    private $config = [
        'host' => null,
        'database' => null,
        'username' => null,
        'password' => null,
        'charset' => null
    ];

    public function __construct($config = null)
    {
        $this->config = $config ?? [
            'host' => $_ENV['DB_HOST'],
            'database' => $_ENV['DB_DATABASE'],
            'username' => $_ENV['DB_USERNAME'],
            'password' => $_ENV['DB_PASSWORD'],
            'charset' => null
        ];

        $this->connect($this->config);
    }

    private function connect($connection)
    {
        $this->setConnect($connection['host'], $connection['database'], $connection['username'], $connection['password'], $connection['charset']);
        $this->open();
        return $this;
    }
}
