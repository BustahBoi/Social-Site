<?php
declare(strict_types=1);
namespace Project\Controllers;
use Project\Database\MySQL;
use Project\Database\MySQLResult;

if(file_exists('..\Config\Autoloader.php'))
{
    require_once '..\Config\Autoloader.php';
}
if(file_exists('.\Config\Autoloader.php'))
{
    require_once '.\Config\Autoloader.php';
}


class DatabaseController
{
    protected $host;
    protected $user;
    protected $pass;
    protected $name;
    public $db;

    public function __construct(array $connection) {
        $this->host = $connection['host'];
        $this->user = $connection['user'];
        $this->pass = $connection['pass'];
        $this->name = $connection['name'];
    }

    public function createConnection()
    {
        $this->db = new MySQL($this->host, $this->user, $this->pass, $this->name);
        $this->db->createDatabase();
        $this->db->selectDatabase();
    }

    public function selectConnection()
    {
        $this->db = new MySQL($this->host, $this->user, $this->pass, $this->name);
        $this->db->selectDatabase();
    }



    public function dropTable($table) {
        $sql = "drop table if exists " . $table;
        $this->db->query($sql);
    }

    public function getTableColumns($table, $columns = array()) : array
    {
        $sql = "select ". implode(",", $columns) . "from " . $table;
        $result = $this->db->query($sql);
        return $result->fetch();
    }

    public function getTableColumnWhere($table, $columns = array(), $criteria) {
        $sql = "select ". implode(",", $columns) . "from " . $table;
    }

    public function createUser(string $user, string $password)
    {
        if($this->getUser($user)->size() === 0) {
            $password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users VALUES(NULL, '$user', '$password')";
            $this->db->query($sql);
            $theuser = $this->getUser($user);
            $theuser = $theuser->fetch();
            $theuser = $theuser['userId'];
            $this->createProfile($theuser);
        }
    }

    public function createProfile(string $theuser)
    {
        $sql = "INSERT INTO profile Values(null, $theuser
                , 'https://www.labradortraininghq.com/wp-content/uploads/2014/02/how-to-crate-train-a-puppy-happy-lab-1.jpg'
                , 'The Jungle', 'I like bagchal!'
                , 'nothing...yet')";
        $this->db->query($sql);
    }

    public function editProfile(string $user, string $location, string $picture, string $motto, string $about)
    {
        $sql = "update profile
                set profileLocation = '$location', profilePic = '$picture', profileMotto = '$motto', profileAbout = '$about'
                where userId = '$user'";
        $this->db->query($sql);
    }

    public function checkUser(string $user, string $password)
    {
        $user = $this->getUser($user);
        if($user->size() !== 0) {
            $user = $user->fetch();
            if(password_verify($password, $user['userPassword']))
            {
                $_SESSION['userId'] = $user['userId'];
            }
        }
    }

    public function login(string $user, string $password) {
        $user = $this->getUser($user)->fetch();
        var_dump($user);
        //if($user->size() !== 0) {
            //$user = $user->fetch();
            //if(password_verify($password, $user['userPassword']))
            //{
               // $_SESSION['userId'] = $user['userId'];
            //}
        //}
    }

    public function userExists($user) : bool
    {
        if($this->getUser($user)->size() !== 0) {
            return true;
        }
        return false;
    }

    private function getUser(string $user) : MySQLResult
    {
        $sql = "select * from users where username = '$user'";
        //var_dump($sql);
        $result = $this->db->query($sql);
        return $result;
    }

    public function getUserFromId($user)
    {
        $sql = "select * from users where userId = '$user'";
        $result = $this->db->query($sql);
        return $result;
    }

    public function getTopicsMain() : MySQLResult
    {
        $sql = "select topicNo, topicTitle, users.userId, userName, 
                DATE_FORMAT(topicDate, '%M %d %Y') from topic, users 
                where topic.userId = users.userId";
        $result = $this->db->query($sql);
        return $result;
    }

    public function createTopic(string $user,string $title, string $content)
    {
        $sql = "insert into topic values(null, '$user', '$title', now(), '$content')";
        $this->db->query($sql);
    }

    public function getTopicFromId(string $topic)// : MySQLResult
    {
        $sql = "select * from topic where topicNo = '$topic'";
        $result = $this->db->query($sql);
        return $result;
    }

    public function getPosts(string $topicid) : MySQLResult
    {
        $sql = "select username, postContent, DATE_FORMAT(postDate, '%M %d %r') from post, users where post.userId = users.userId and topicNo = '$topicid' order by postDate";
        $result = $this->db->query($sql);
        return $result;
    }

    public function createPost(string $topic, string $user, string $content) //: MySQLResult
    {
        $sql = "insert into post values(null, '$topic', '$user', now() ,'$content')";
        $this->db->query($sql);
    }

    public function searchGeneral(string $search, string $user)
    {
        $sql = "CREATE OR REPLACE VIEW ".$user."_search AS
                select t.topicNo, t.topicTitle,t.topicContent,t.topicDate
                from topic t
                where match(t.topicTitle, t.topicContent) against('$search')
                union
                select p.topicNo, p.postNo, p.postContent, p.postDate
                from post p
                where match(p.postContent) against('$search');";
        $this->db->query($sql);
    }

    public function searchView(string $user) : MySQLResult
    {
        $sql = "SELECT * FROM ".$user."_search";
        $result = $this->db->query($sql);
        return $result;
    }
    //used for forum recent posts
    public function getRecent() {
        $sql = "select t.topicNo, topicTitle, u.userId, u.userName, p.postDate, postContent
                from post p left join users u using (userId) 
                right join topic t using (topicNo) where u.userId is not null
                order by p.postDate desc limit 10;";
        $result = $this->db->query($sql);
        return $result;
    }

    public function getProfile(string $user) {
        $sql = "select * from profile where userId = $user";
        $result = $this->db->query($sql);
        return $result;
    }

    public function getMessagesRecieved(string $user) {
        $sql = "select u.userId, u.userName, messageSubject, messageBody, messageTime 
                from users u inner join userMessages um on u.userId = um.userIdFrom 
                inner join messages m on um.messageId = m.messageId where um.userIdTo = $user";
        $result = $this->db->query($sql);
        return $result;
    }

    public function getMessagesSent(string $user) {
        $sql = "select u.userId, u.userName, messageSubject, messageBody, messageTime 
                from users u inner join userMessages um on u.userId = um.userIdTo 
                inner join messages m on um.messageId = m.messageId where um.userIdFrom = $user";
        $result = $this->db->query($sql);
        return $result;
    }

    public function sendMessage(string $userFrom, string $userTo, string $title, string $content) {
        if($this->userExists($userTo)) {
            $this->createMessage($title, $content);
            $messageId = $this->getLastMessageId();
            $aUserTo = $this->getUserId($userTo);
            $sql = "insert into usermessages values($messageId, $userFrom, $aUserTo)";
            $this->db->query($sql);
        }

    }

    private function getLastMessageId() {
        $sql = "select messageId from messages order by messageId desc limit 1";
        $result = $this->db->query($sql);
        $result = $result->fetch();
        $result = $result['messageId'];
        return $result;

    }

    public function getUserId(string $user) {
        $aUser = $this->getUser($user);
        $aUser = $aUser->fetch();
        $aUser = $aUser['userId'];
        return $aUser;
    }

    private function createMessage(string $title, string $content) {
        $sql = "insert into messages values(null, '$title', '$content', now())";
        $this->db->query($sql);
    }

}

