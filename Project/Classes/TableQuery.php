<?php
namespace Project\Classes;

use Project\Controllers\DatabaseController;
use Project\Database\MySQLResult;
use Project\Interfaces\QueryDataInterface;
use Project\Interfaces\TableMakerInterface;

class TableQuery
{
    protected $tableMaker;
    protected $queryParser;
    protected $config;
    protected $db;
    protected $queryArr;
    protected $page;
    protected $link;
    protected $headers;
    protected $tableName;

    public function __construct(DatabaseController $db, TableMakerInterface $tableMaker, QueryDataInterface $queryData, array $ini) {
        $this->db = $db;
        $this->tableMaker = $tableMaker;
        $this->queryParser = $queryData;
        $this->config = $ini;
    }

    public function tableQuery() {
        if($this->tableName == "forumTableMain") {
            $query = $this->db->getTopicsMain();
        }
        if($this->tableName == "forumTableAlt") {
            $query = $this->db->getRecent();
        }
        if($this->tableName == "topicTableMain") {
            $query = $this->db->getPosts($_GET['topicId']);
        }
        if($this->tableName == "searchTableMain") {
            $query = $this->db->searchView($_SESSION['userId']);
        }
        if($this->tableName == "messageTableMain") {
         if($_GET['type'] == 'sent') {
             $query = $this->db->getMessagesSent($_SESSION['userId']);
         } else {
             $query = $this->db->getMessagesRecieved($_SESSION['userId']);
         }
        }
        $this->setQueryData($query);
    }

    public function setQueryData(MySQLResult $query)
    {
        $this->queryParser->setQueryResult($query);
        $this->queryParser->parseQuery();
        $this->queryArr = $this->queryParser->getData();
        $this->queryParser->clearData();
    }
    public function setTableName(string $name) {
        $this->tableName = $name;
    }
    public function test() {
        $this->tableQuery();
        $this->getHeaders();
        $data  = $this->parseQuery();
        $this->tableMaker->setData($this->headers, $data);
        $table = $this->tableMaker->createTable();
        return $table;
    }

    public function getHeaders()
    {
        $headers = array();
        $tableConfig = $this->config[$this->tableName];
        foreach($tableConfig as $key => $value) {
            array_push($headers, $key);
        }
        $this->headers = $headers;
    }

    public function parseQuery()
    {
        $parsed = array();
        foreach($this->queryArr as $aRow) {
            $row = array();
            foreach ($aRow as $key => $value) {
                $result = null;
                if ($this->isId($key)) {
                    $this->link = $value;
                    $this->page = substr($key, 0, -2);
                } else if ($this->colTitle($key)) {
                    $result = $this->rowLink($value);
                }else if ($key == "postContent") {
                    if ($this->checkTag($value)) {
                        $result = $this->setTag($value);
                    } else {
                        $result = $value;
                    }
                } else {
                    $result = $value;
                }
                if(!is_null($result)) {
                    array_push($row, $result);
                }
            }
            array_push($parsed, $row);
        }
        return $parsed;
    }

    private function isId(string $key) {
        if($key == "userId" || $key == "topicNo") {
            return true;
        }
        return false;
    }

    private function colTitle(string $key) : bool
    {
        if($key == "userName" || $key == 'topicTitle') {
            return true;
        }
        return false;
    }

    private function checkTag(string $value) : bool
    {
        $words = explode(' ', $value);
        foreach($words as $word) {
            if(!is_null(strpos($word, '@'))) {
                return true;
            }
        }
        return false;
    }

    private function setTag(string $value) : string
    {
        $parsed = array();
        $words = explode(' ', $value);
        foreach($words as $word) {
            if(!is_null(strpos($word, '@'))) {
                $user = substr($word, strpos($word, '@')+1);
                if($this->db->userExists($user)) {
                    $userId = $this->db->getUserId($user);
                    $thing = "<a href='./main.php?page=user&userId=$userId'>$user</a>";
                    array_push($parsed, $thing);
                } else {
                    array_push($parsed, $word);
                }
            } else {
                array_push($parsed, $word);
            }
        }
        $result = implode(' ', $parsed);
        return $result;
    }

    private function rowLink(string $value) : string
    {
        $result = "<a href='./main.php?page=$this->page&$this->page"."Id=$this->link' class='btn btn-default' role='button'>$value</a>";
        return $result;
    }

}