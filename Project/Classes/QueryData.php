<?php
namespace Project\Classes;
use Project\Database\MySQLResult;
use Project\Interfaces\QueryDataInterface;

class QueryData implements QueryDataInterface
{
    protected $MySQLResult;
    protected $data;
    function __construct() {
        $this->data = array();
    }

    public function clearData()
    {
        $this->data = array();
    }

    public function getData() : array
    {
        return $this->data;
    }

    public function parseQuery()
    {
        if ($this->validResult()) {
            while ($data = $this->MySQLResult->fetch()) {
                array_push($this->data, $data);
            }
        }
    }

    private function validResult() : bool
    {
        if($this->MySQLResult->size() != 0) {
            return true;
        }
        return false;
    }

    public function setQueryResult(MySQLResult $result)
    {
        $this->MySQLResult = $result;
    }
}