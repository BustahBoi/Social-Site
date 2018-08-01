<?php
namespace Project;
use Project\Classes\TableQuery;
use Project\Classes\TableMaker;
use Project\Classes\QueryData;
header('Content-Type: text/html; charset=utf-8');
require_once '.\functions.php';
require_once '.\Config\Autoloader.php';
session_save_path('./');
session_start();


$db = connect();
$db->selectConnection();

setSession($_GET);

sessionDefaults($_SESSION);

$page = currentPage($_SESSION);

$_SESSION['page'] = $page;

$main = file_get_contents('.\HTML\main.html');
$change = getIni();
$tableMaker = new TableMaker();
$queryData = new QueryData();
$TableQuery = new TableQuery($db, $tableMaker,$queryData, $change);
$main = checkPost($db, $_POST, $_SESSION, $main);
$main = str_replace('##content##', getPage($page), $main);


$main = checkGet($db, $_GET, $_SESSION, $main);




if(isset($_SESSION['userId'])) {
    $main = str_replace("##user##", getUserFromId($db, $_SESSION['userId']), $main);
    $main = str_replace("##userLink##", "./main.php?page=user&userId=".$_SESSION['userId'], $main);
    $main = str_replace("##log##", "
            <a class='btn btn-warning' href='./main.php?page=message&type=received'>##message##</a>
            <form class=\"form-inline my-sm-0\" action=\"./main.php?page=home\" method=\"post\">
            <button class=\"btn btn-secondary my-2 my-sm-0\" name=\"logout\" value=\"logout\" type=\"submit\">##logout##</button>
        </form>", $main);
}else {
    $main = str_replace("##user##", $change["login"][$_SESSION['language']], $main);
    $main = str_replace("##userLink##", "./main.php?page=login", $main);
    $main = str_replace('##log##', "", $main);
}

//change common language elements and specific site elements on the page
$main = pageReplace($main, $change[$_SESSION['site'].$_SESSION['language']]);


$main = pageReplace($main, $change[$_SESSION['language']]);


if(strpos($main, '||')) {
    preg_match_all('|\|\||', $main, $matches);

    if(sizeof($matches[0])/2 > 1) {
        $pos1 = strpos($main, '||');
        $pos2 = strpos($main, '||', $pos1 + 2);
        $table1 = substr($main, $pos1, $pos2 - $pos1 + 2);
        $table1Name = substr($main, $pos1 + 2, $pos2 - $pos1 - 2);
        $TableQuery->setTableName($table1Name);
        $newTable = $TableQuery->test();
        $main = str_replace($table1, $newTable , $main);


        $pos1 = strpos($main, '||');
        $pos2 = strpos($main, '||', $pos1 + 2);
        $table2 = substr($main, $pos1, $pos2 - $pos1 + 2);
        $table2Name = substr($main, $pos1 + 2, $pos2 - $pos1 - 2);
        $TableQuery->setTableName($table2Name);
        $newTable = $TableQuery->test();
        $main = str_replace($table2, $newTable , $main);
    } else {
        $pos1 = strpos($main, '||');
        $pos2 = strpos($main, '||', $pos1 + 2);
        $table = substr($main, $pos1, $pos2 - $pos1 + 2);
        $tableName = substr($main, $pos1 + 2, $pos2 - $pos1 - 2);
        $TableQuery->setTableName($tableName);
        $newTable = $TableQuery->test();
        $main = str_replace($table, $newTable , $main);
    }
}

if(isset($_SESSION['userId'])) {
    $main = str_replace('##userhtml##', $_SESSION['userId'], $main);
}

if(isset($_GET['type'])) {
    if($_GET['type'] == "sent") {
        $main = str_replace('##type##', '<a class="btn btn-outline-warning" href="./main.php?page=message&type=received">Received messages</a>', $main);
    } else if($_GET['type'] == "received") {
        $main = str_replace('##type##', '<a class="btn btn-outline-warning" href="./main.php?page=message&type=sent">Sent Messages</a>', $main);
    }
}


echo $main;