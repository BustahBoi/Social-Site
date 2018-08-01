<?php
namespace Project\Interfaces;
use Project\Database\MySQLResult;


interface QueryDataInterface
{
    public function clearData();
    public function parseQuery();
    public function getData() : array;
    public function setQueryResult(MySQLResult $result);
}