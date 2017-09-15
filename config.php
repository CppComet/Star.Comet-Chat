<?php
/**
 * Apache License 2.0
 * @author Trapenok Victor, Levhav@ya.ru, 89244269357
 * I will be glad to new orders for the development of anything.
 *
 * Levhav@ya.ru
 * Skype:Levhav
 * 89244269357
 *
 * https://github.com/Levhav/Star.Comet-Chat
 */

include_once $_SERVER['DOCUMENT_ROOT'].'/config.php';


$conf = array();

/**
 * ip address from which it is allowed to call api methods of chat management
 * Or false if the restriction is disabled (not secure)
 */
$conf['trusted_ip'] = $_SERVER['SERVER_ADDR'];       // For example "159.8.8.107"
$conf['max_img_size'] = 1024*1024*4;   // Maximum size of uploaded image
$conf['cookie_expire'] = 3600*24;                    // Lifetime authentication cookie
 
/**
 * the address of the directory from which the chat works is used in the admin panel
 */
$conf['home_dir'] = "https://comet-server.com/doc/CometQL/Star.Comet-Chat";
$conf['host_name'] = "https://comet-server.com";

/**
 * api the key for the work of yandex translator
 * Receive here https://tech.yandex.ru/translate/
 */
$conf['yandex_translate_key'] = "trnsl.1.1.20150628T023941Z.e709b71aa86ebce5.3bd7a5a621ac9d1e1c5fe7ba87afebf351fda5ca";

/**
 * Image download folder
 */
$conf['file_dir'] = "chatFiles";


/**
 * Fragment of url of the address of the user profile page
 * used in the admin panel
 */
$conf['user_url_tpl'] = "https://comet-server.com/doc/CometQL/Star.Comet-Chat/backend-example/userPage.php?name=";
 
/**
 * Part of the way to avatars
 * @type String
 * Example https://comet-server.com/avatar/
 * And then the user's login will be added
 */
$conf['user_avatar_url_tpl'] = ""; 
  
/**
 * Access to the database
 */
$conf['mysql_db'] = "StarCometChat";
$conf['mysql_user'] = "StarCometChat";
$conf['mysql_pw'] = "RLUJ4TXE22XL5JTh";
$conf['mysql_host'] = "localhost"; 
/**
 * Access to the comet server
 * Get access keys here https://comet-server.com/#price
 */
$conf['cometQL_dev_id'] = 15;
$conf['cometQL_key'] = "lPXBFPqNg3f661JcegBY0N0dPXqUBdHXqj2cHf04PZgLHxT6z55e20ozojvMRvB8";

/**
 * Allows access only for requests from trusted ip addresses
 */
function testIpOrDie()
{
    if ( getConfArray("trusted_ip") != false && $_SERVER['REMOTE_ADDR'] != getConfArray("trusted_ip"))
    {
        die("No access from ip ".$_SERVER['REMOTE_ADDR']);
    }
}

function getConfArray($val)
{
    global $conf;
    return $conf[$val];
}

/**
 * URL to request an authentication hash
 * Used only in getUsersHash
 */
$conf['URL_getUsersHash'] = 'https://comet-server.com/doc/CometQL/Star.Comet-Chat/backend-example/chat_get_user_hash.php';
/**
 * URL to request information about users in json
 * Used only in getUsersInfo
 */
$conf['URL_getUsersInfo'] = 'https://comet-server.com/doc/CometQL/Star.Comet-Chat/backend-example/chat_get_users.php'; 
 


header("Content-Type: text/html; charset=utf-8");
header("Access-Control-Allow-Origin: *");

ini_set('display_errors','on');
error_reporting(E_ALL);

class StarCometChat
{
    /**
     * @var app
     */
    private static $conf = false;

    public $db = false;
    public $comet = false;

    /**
     * List of id users with administrator rights
     * @var array
     */
    private $admin_ids = array();

    /**
     * Number of history messages downloaded for 1 time
     * Must match what is specified in chart.js
     * @var int
     */
    public $page_size = 30;

    private function __construct()
    {
        global $conf;
    }

    /**
     * @return app
     */
    public static function conf()
    {
        if(!self::$conf)
        {
            self::$conf = new StarCometChat();
        }
        return self::$conf;
    }

    public function getAdminIds()
    { 
        /*
         * An example of how to select a list of admins from the database if the chat is integrated into the site's database and not as a separate web application with a separate database
        if(count($this->admin_ids) > 0)
        {
            return $this->admin_ids;
        }
        
        $result = app::dbQuery("SELECT id FROM `users` where status in(".USER_STATUS_MODERATOR.", ".USER_STATUS_ADMINISTRATOR.")");
        if(!$result || !mysqli_num_rows($result))
        {
            return $this->admin_ids;
        }
        else
        {
            while($row = mysqli_fetch_assoc($result))
            {
                $this->admin_ids[] = $row['id'];
            }
        }*/
        $this->admin_ids = array(1, 2, 3);
        return $this->admin_ids; 
    }

    public function getDB()
    {
        if(!$this->db)
        {
            /**
             * Access to the database
             */
            $this->db = mysqli_connect(getConfArray('mysql_host'), getConfArray('mysql_user'), getConfArray('mysql_pw'), getConfArray('mysql_db'));
            if ( !$this->db ) die ("Can not connect to MySQL");

            mysqli_query($this->db, "SET CHARACTER SET 'utf8'");
        }

        return $this->db;
    }

    public function getComet()
    {
        if(!$this->comet)
        {
            /**
             * Access to the comet server
             */
            $this->comet = mysqli_connect("app.comet-server.ru", getConfArray('cometQL_dev_id'), getConfArray('cometQL_key'), "CometQL_v1");
            if ( !$this->comet ) die ("Can not connect to CometQL");
        }

        return $this->comet;
    }
}

/**
 * Returns information about users
 * @param array $arr
 * @return object
 *
 * Example of a valid response
 * [array(
 *  "user_id" => 6,
 *  "avatar_url" => "https://comet-server.com/doc/CometQL/Star.Comet-Chat/img/avatar0.png",
 *  "name" => "Артём",
 *  "city" => "Артём",
 *  "age" => 12,
 *  "status" => "active",
 *  "login" => "lodin6"
 * ),
 * array(
 *  "user_id" => 6,
 *  "avatar_url" => "https://comet-server.com/doc/CometQL/Star.Comet-Chat/img/avatar0.png",
 *  "name" => "Артём",
 *  "city" => "Артём",
 *  "age" => 12,
 *  "status" => "active",
 *  "login" => "lodin6"
 * )]
 *
 */
function getUsersInfo($arr)
{
    
    if(is_array($arr))
    {
        $arr = implode(',', $arr);
    }
    else
    {
        $arr = (int)$arr;
    }

    if( $curl = curl_init())
    {
        curl_setopt($curl, CURLOPT_URL, getConfArray('URL_getUsersInfo'));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl, CURLOPT_USERAGENT,  $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, "users=".$arr);
        $out = curl_exec($curl); 
        curl_close($curl);
        return json_decode($out, 'true');
    }
    return false;
    
    /**
    * An example of how to select information about users from the database if the chat is integrated into the site's database and not as a separate web application with a separate database
    if(is_array($arr))
    {
        $arr = preg_replace("#[^0-9,]#", "", implode(',', $arr));
    }
    else
    {
        $arr = (int)$arr;
    }

    $users = array();
    $result = app::dbQuery("SELECT id as user_id, name, '' as city, YEAR(Now()) - YEAR(birthdate) as age, 'active' as status, login, avatar_url  FROM `users` where id in(".$arr.")");
    if(!$result || !mysqli_num_rows($result))
    {
        return false;
    }
    else
    {
        while($row = mysqli_fetch_assoc($result))
        {
            if(empty($row['avatar_url']))
            {
                $row['avatar_url'] = app::conf()->default_avatar_url;
            }
            $users[] = $row;
        }
    }

    return $users;*/
}

/**
 * Returns the authorization hash of the user
 * @param int $user_id
 * @return string hash of user authorization on comet server
 */
function getUsersHash($user_id)
{ 
    if(is_array($arr))
    {
        $arr = implode(',', $arr);
    }
    else
    {
        $arr = (int)$arr;
    }

    if( $curl = curl_init())
    {
        curl_setopt($curl, CURLOPT_URL, getConfArray('URL_getUsersHash'));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl, CURLOPT_USERAGENT,  $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, "id=".$arr);
        $out = curl_exec($curl);
        curl_close($curl);
        return $out;
    }
    return "";
    
    /**
    * An example of how to select information about users from the database if the chat is integrated into the site's database and not as a separate web application with a separate database
    $result = app::dbQuery("SELECT comet_hash FROM `users` where id = ':1' ", (int)$user_id);

    if(!$result || !mysqli_num_rows($result))
    {
        return "";
    }

    $row = mysqli_fetch_assoc($result);
    if(empty($row['comet_hash']))
    {
        $login = new login();
        $login->updateCometKey($user_id);
    }
    else
    {
        app::cometQuery("INSERT INTO users_auth (id, hash )VALUES (':1', ':2')", $user_id, $row['comet_hash']);
    }

    return $row['comet_hash'];*/
}

/**
 * additional access check
 * @param type $user_id
 */
function additionalChecksAccess($user_id)
{
    /**
    * An example of how to check whether a user has the right to use a chat from the database if the chat is integrated into the database site and not as a separate web application with a separate database
    $result = mysqli_query(StarCometChat::conf()->getDB(), "SELECT * FROM `users` WHERE (status > 3 or use_chat_to > ".date("U").") and id = ".$user_id);
    if(!mysqli_num_rows($result))
    { 
        die("Access is denied. Pay for using chat.");
    }
     */
    
    return true;
}
