<?php

namespace Oforostianyi\Recipient;
class DatabaseConnection
{
    /**
     * @param string $host
     * @param string $username
     * @param string $password
     * @param string $database
     * @throws \Exception
     */
    public function __construct(string $host, string $username, string $password, string $database)
    {
        $this->connection = new \mysqli($host, $username, $password, $database);
        if ($this->connection->connect_error) {
            throw new \Exception('Database connection failed: ' . $this->connection->connect_error);
        }
    }

    /**
     * @return \mysqli
     */
    public function getConnection(): \mysqli
    {
        return $this->connection;
    }
}