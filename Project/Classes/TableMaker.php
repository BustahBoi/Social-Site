<?php
namespace Project\Classes;
use Project\Interfaces\TableMakerInterface;
class TableMaker implements TableMakerInterface
{
    protected $headers;
    protected $data;
    public function setData(array $headers, array $data)
    {
        $this->headers = $headers;
        $this->data = $data;
    }
    private function clearData() {
        $this->headers = null;
        $this->data = null;
    }
    public function createTable() : string
    {
        $result = '<table class="table table-light table-hover">';
        $result .= $this->setTableHead();
        $result .= $this->setTableRows();
        $result .= '</table>';
        $this->clearData();
        return $result;
    }
    private function setTableHead() : string
    {
        $result = '<thead>';
        $result .= '<tr>';
        foreach($this->headers as $value) {
                $result .= "<th>$value</th>";
        }
        $result .= '</tr>';
        $result .= '</thead>';
        return $result;
    }
    private function setTableRows() : string
    {
        $result = "";
        foreach($this->data as $row) {
            $result .= '<tr>';
            foreach($row as $value) {
                $result .= '<td>';
                $result .= $value;
                $result .= '</td>';
            }
            $result .= "</tr>";
        }
        return $result;
    }
}