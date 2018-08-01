<?php
namespace Project;
use Project\Controllers\DatabaseController;


function pageReplace($page, $array) : string
{
    foreach($array as $key => $value) {
        $page = str_replace($key, $value, $page);
    }
    return $page;
}

function sessionDefaults(array $session)
{
    if(!isset($session['site']) && !isset($session['language']))
    {
        $_SESSION['site'] = 'Bagchal';
        $_SESSION['language'] = 'English';
    }
}

function currentPage(array $session) : string
{
    if(isset($session['page'])) {
        return $session['page'];
    } else {
        return "home";
    }
}

function getPage($page) : string
{
    $main = file_get_contents('.\HTML\\' . $page .'.html');
    return $main;
}

function setSession(array $gets)
{
    foreach($gets as $key => $value) {
        if($key != "userId") {
            $_SESSION[$key] = $value;
        }
    }
    if(empty($gets) &&  $_SERVER['REQUEST_METHOD'] == 'POST') {
        $_SESSION['page'] = 'home';

    }

}

function getIni() : array //returns the ini file, bad practice
{
    $ini = parse_ini_file(realpath(__DIR__ . '/Config/Config.ini'), true);
    return $ini;
}

function tableSettings(string $type) : array
{
    $site = returnPage();
    $ini = getIni();
    $item = $site . "Table" . $type;
    $item = $ini[$item];
    return $item;
}

function returnPage() : string
{
    return $_SESSION['page'];
}

function connect() {
    $ini = getIni();
    $connection = new DatabaseController($ini['Connection']);
    return $connection;
}

function checkPost(DatabaseController $db, array $post, array &$session, string $main)
{
    $result = $main;
    if(isset($post['register-username']) && isset($post['register-password']))
    {
        $username = htmlentities($post['register-username'], ENT_QUOTES, 'UTF-8');
        $password = htmlentities($post['register-password'], ENT_QUOTES, 'UTF-8');
        $db->createUser($username, $password);
    }
    if(isset($post['login-username']) && isset($post['login-password'])) {
        $username = htmlentities($post['login-username'], ENT_QUOTES, 'UTF-8');
        $password = htmlentities($post['login-password'], ENT_QUOTES, 'UTF-8');
        $db->checkUser($username, $password);
    }
    if(isset($session['userId'])) {
        if(isset($post['topic-title']) && isset($post['topic-content'])) {
            $title = htmlentities($post['topic-title'], ENT_QUOTES, 'UTF-8');
            $content = htmlentities($post['topic-content'], ENT_QUOTES, 'UTF-8');
            $db->createTopic($session['userId'], $title, $content);
        }
        if(isset($post['post-content'])) {
            $content = htmlentities($post['post-content'], ENT_QUOTES, 'UTF-8');
            $db->createPost($session['topicId'], $session['userId'], $content);
        }
        if(isset($post['search'])) {
            $search = htmlentities($post['search'], ENT_QUOTES, 'UTF-8');
            $db->searchGeneral($search, $session['userId']);
        }
        if(isset($post['logout'])) {
            session_destroy();
            header('location: http://localhost/final/Project/main.php?page=home');
        }
        if(isset($post['profile-location']) && isset($post['profile-picture']) && isset($post['profile-motto']) && isset($post['profile-aboutme']) ) {
                $location = htmlentities($post['profile-location'], ENT_QUOTES, 'UTF-8');
                $picture = htmlentities($post['profile-picture'], ENT_QUOTES, 'UTF-8');
                $motto = htmlentities($post['profile-motto'], ENT_QUOTES, 'UTF-8');
                $aboutme = htmlentities($post['profile-aboutme'], ENT_QUOTES, 'UTF-8');
                $db->editProfile($session['userId'], $location, $picture, $motto, $aboutme);
        }
        if(isset($post['message-user']) && isset($post['message-title']) && isset($post['message-content'])) {
            $userTo = htmlentities($post['message-user'], ENT_QUOTES, 'UTF-8');
            $title = htmlentities($post['message-title'], ENT_QUOTES, 'UTF-8');
            $content = htmlentities($post['message-content'], ENT_QUOTES, 'UTF-8');
            $db->sendMessage($session['userId'], $userTo, $title, $content);
        }
    }
    return $result;
}

function getUserFromId(DatabaseController $db,$userId)// : string
{
    $user = $db->getUserFromId($userId);
    $user = $user->fetch();
    $user = $user['userName'];
    return $user;
}

function checkGet(DatabaseController &$db, array &$get, array &$session, string $page)
{
    $result = $page;
    if(isset($get['page']) && isset($get['topicId'])) {
        $topic = getTopicFromId($db, $get['topicId']);
        $result = str_replace("##dbtopictitle##", $topic['topicTitle'], $result);
        $result = str_replace("##dbtopiccontent##", $topic['topicContent'], $result);
    }

    if(isset($get['page']) && isset($get['userId']) && isset($session['userId'])) {
        if($get['userId'] != $session['userId']) {
            $userId = $get['userId'];
            $user = getUserFromId($db, $userId);
            $result = str_replace("##profileuser##", $user, $result);
            //database profile stuff
            $profile = getProfile($db, $userId);
            $pic = $profile['profilePic'];
            $about = $profile['profileAbout'];
            $location = $profile['profileLocation'];
            $motto = $profile['profileMotto'];
            $result = str_replace("##profilepicture##", $pic, $result);
            $result = str_replace("##profileabout##", $about, $result);
            $result = str_replace("##profilelocation##", $location, $result);
            $result = str_replace("##profilemotto##", $motto, $result);
        } else {
        //edit the page
            $userId = $get['userId'];
            $user = getUserFromId($db, $userId);
            $result = str_replace("##profileuser##", $user, $result);
            //database profile stuff
            $profile = getProfile($db, $userId);
            $pic = $profile['profilePic'];
            $about = $profile['profileAbout'];
            $edit = file_get_contents('.\HTML\profile.html');
            $location = $profile['profileLocation'];
            $motto = $profile['profileMotto'];
            $result = str_replace("##profilepicture##", $pic, $result);
            $result = str_replace("##profileabout##", $about . $edit, $result);
            $result = str_replace("##profilelocation##", $location, $result);
            $result = str_replace("##profilemotto##", $motto, $result);
        }
    }
    return $result;
}

function getProfile(DatabaseController $db, string $userId) : array
{
    $profile = $db->getProfile($userId);
    $profile = $profile->fetch();
    return $profile;
}

function getTopicFromId(DatabaseController $db, string $topicid) : array
{
    $topic = $db->getTopicFromId($topicid);
    $topic = $topic->fetch();
    return $topic;
}











