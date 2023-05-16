<?php

namespace Oforostianyi\Recipient;
class DatabaseConnection
{
    public function __construct(string $host, string $username, string $password, string $database)
    {
        $this->connection = new \mysqli($host, $username, $password, $database);
        if ($this->connection->connect_error) {
            throw new \Exception('Database connection failed: ' . $this->connection->connect_error);
        }
    }

    public function getConnection(): \mysqli
    {
        return $this->connection;
    }
}