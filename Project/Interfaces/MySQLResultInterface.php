<?php
declare(strict_types=1);
namespace Project\Interfaces;

interface MySQLResultInterface
{
    public function __construct(&$mysql, $query);
    public function size() : int;
    public function fetch();
    public function insertID();
}