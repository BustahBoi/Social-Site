<?php
declare(strict_types=1);
namespace Project\Database;
use Project\Interfaces\MySQLInterface;

if(file_exists('..\Config\Autoloader.php'))
{
    require_once '..\Config\Autoloader.php';
}
if(file_exists('.\Config\Autoloader.php'))
{
    require_once '.\Config\Autoloader.php';
}

class MySQL implements MySQLInterface
{
    protected $host;
    protected $dbUser;
    protected $dbPass;
    protected $dbName;
    protected $dbConn;
    protected $dbconnectError;

    public function __construct(string $host,string $dbUser,string $dbPass,string $dbName)
    {
        $this->host = $host;
        $this->dbUser = $dbUser;
        $this->dbPass = $dbPass;
        $this->dbName = $dbName;
        $this->connectToServer();
    }

    public function connectToServer() //: void //PHP 7.1 supports the use of void
    {
        $this->dbConn = mysqli_connect($this->host, $this->dbUser, $this->dbPass);
        if(!$this->dbConn)
        {
            trigger_error('could not connect to server');
            $this->dbconnectError = true;
        }
    }

    public function selectDatabase()//: void //PHP 7.1 supports the use of void
    {
        if(!mysqli_select_db($this->dbConn, $this->dbName))
        {
            trigger_error('could not select database');
            $this->dbconnectError = true;
        }
    }

    public function dropDatabase() : string
    {
        $sql = "drop database $this->dbName";
        $result = "<br> $sql <br>";
        if($this->query($sql))
        {
            $result .= "<br> the $this->dbName database was dropped<br>";
        }
        else
        {
            $result .= "<br>the $this->dbName database was not dropped<br>";
        }
        return $result;
    }

    public function createDatabase() : string
    {
        $sql = "create database if not exists $this->dbName";
        $result = "<br> $sql <br>";
        if($this->query($sql))
        {
            $result .= "the $this->dbName database was created<br>";
        }
        else
        {
            $result .= "the $this->dbName database was not created<br>";
        }
        return $result;
    }

    protected function isError() : bool
    {
        if($this->dbconnectError)
        {
            return true;
        }
        $error = mysqli_error($this->dbConn);
        if(empty($error))
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    public function createTable(string $table,string $sql) : string
    {
        $result = $db->query($sql);
        if($result == true)
        {
            $result = "$table was added<br>";
        }
        else
        {
            $result = "$table was not added<br>";
        }
        return $result;
    }

    public function query(string $sql) //: MySQLResult
    {
        mysqli_query($this->dbConn, "set character_set_results='utf8'");
        if(!$queryResource = mysqli_query($this->dbConn, $sql))
        {
            trigger_error('Query Failed: <br>' . mysqli_error($this->dbConn) . '<br> SQL: ' . $sql);
            return false;
        }
        return new MySQLResult($this, $queryResource);
    }
}