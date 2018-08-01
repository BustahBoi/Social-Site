<?php
declare(strict_types=1);
namespace Project\Interfaces;

interface TableMakerInterface
{
    public function setData(array $headers, array $data);
    public function createTable() : string;
}