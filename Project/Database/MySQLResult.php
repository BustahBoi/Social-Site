<?php
declare(strict_types=1);
namespace Project\Database;
use Project\Interfaces\MySQLResultInterface;
if(file_exists('..\Config\Autoloader.php'))
{
    require_once '..\Config\Autoloader.php';
}
if(file_exists('.\Config\Autoloader.php'))
{
    require_once '.\Config\Autoloader.php';
}

class MySQLResult implements MySQLResultInterface
{
    protected $mysql;
    protected $query;
    public function __construct(&$mysql, $query)
    {
        $this->mysql = &$mysql;
        $this->query = $query;
    }

    public function size() : int
    {
        return mysqli_num_rows($this->query);
    }

    public function fetch()
    {
        if ( $row = mysqli_fetch_array( $this->query , MYSQLI_ASSOC ))
        {
            return $row;
        }
        else if ( $this->size() > 0 )
        {
            mysqli_data_seek ( $this->query , 0 );
            return false;
        }
        else
        {
            return false;
        }
    }

    public function insertID()
    {
        /**
         * returns the ID of the last row inserted
         * @return  int
         * @access  public
         */
        return mysqli_insert_id($this->mysql->dbConn);
    }


    private function isError()
    {
        return $this->mysql->isError();
    }
}