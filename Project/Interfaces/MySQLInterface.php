<?php
declare(strict_types=1);
namespace Project\Interfaces;

interface MySQLInterface
{
    public function __construct(string $host,string $dbUser,string $dbPass,string $dbName);
    public function connectToServer();
    public function selectDatabase();
    public function dropDatabase() : string;
    public function createDatabase() : string;
    public function createTable(string $table,string $sql) : string;
    public function query(string $sql);
}