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

ini_set('display_errors','on');
error_reporting(E_ALL);

/**
 * ip address from which it is allowed to call api methods of chat management
 * Or false if the restriction is disabled (not secure)
 */
$trusted_ip = false;

session_start();

/**
 * Allows access only for requests from trusted ip addresses
 */
function testIpOrDie()
{
    global $trusted_ip;
    if ( $trusted_ip !== false && $_SERVER['REMOTE_ADDR'] != getConfArray("trusted_ip"))
    {
        die("No access from ip ".$_SERVER['REMOTE_ADDR']);
    }
}


/**
 * The function returns an array with information about the user by its identifier
 * Usually information about users is stored in the database, but here for an example in order to simplify the code, the information is hard-coded as an array
 */
function getUserInfoById($id)
{
    $users[] = array();
    $users[] = array("user_id" => 1, "avatar_url" => "https://comet-server.com/doc/CometQL/Star.Comet-Chat/img/avatar0.png", "name" => "Виктор", "city" => "Москва", "age" => 24, "status" => "active", "login" => "lodin1", "error" => "", "status" => false);
    $users[] = array("user_id" => 2, "avatar_url" => "https://comet-server.com/doc/CometQL/Star.Comet-Chat/img/avatar0.png", "name" => "Лена", "city" => "Владивосток", "age" => 24, "status" => "active", "login" => "lodin2");
    $users[] = array("user_id" => 3, "avatar_url" => "https://comet-server.com/doc/CometQL/Star.Comet-Chat/img/avata.png", "name" => "Маша", "city" => "Владивосток", "age" => 0.9, "status" => "active", "login" => "lodin3");
    $users[] = array("user_id" => 4, "avatar_url" => "https://comet-server.com/doc/CometQL/Star.Comet-Chat/img/avata2.png", "name" => "Михаил", "city" => "Москва", "age" => 30, "status" => "active", "login" => "lodin4");
    $users[] = array("user_id" => 5, "avatar_url" => "https://comet-server.com/doc/CometQL/Star.Comet-Chat/img/avatar0.png", "name" => "Иван", "city" => "Хабаровск", "age" => 20, "status" => "active", "login" => "lodin5");
    $users[] = array("user_id" => 6, "avatar_url" => "https://comet-server.com/doc/CometQL/Star.Comet-Chat/img/avatar0.png", "name" => "Артём", "city" => "Артём", "age" => 12, "status" => "active", "login" => "lodin6");

    if(isset($users[(int)$id]))
    {
        return $users[(int)$id];
    }

    return null;
}

/**
 * The function returns an array with information about the user by its lig
 * Usually information about users is stored in the database, but here for an example in order to simplify the code, the information is hard-coded as an array
 */
function getUserInfoByLogin($login)
{
    $id=0;
    do{
        $id++;
        $info = getUserInfoById($id);
        if($info == null)
        {
            return null;
        }
        else if($info['login'] == $login)
        {
            return $info;
        }
        
    }while(true); 
}

/**
 * Returns the authorization hash string for the user by its ID
 */
function getUserHash($user_id)
{
    $salt = $user_id."bgDf5gfDF4VD5bBFg7f8".date("z").getUserInfoById($user_id)['age'].getUserInfoById($user_id)['city'].getUserInfoById($user_id)['login'];
    return md5($salt.md5($salt));
}

// Call when a user logs on to the site
function sendUserLoginInfo($user_id)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://comet-server.com/doc/CometQL/Star.Comet-Chat/usersAuth.php?id=".((int)$user_id)."&hash=".getUserHash($user_id));
    curl_setopt($ch, CURLOPT_TIMEOUT, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $output = curl_exec($ch);
    curl_close($ch);
    
    if($output !="usersAuth-ok")
    {
        echo "<pre>Error accessing api:sendUserLoginInfo:\n";
        echo "https://comet-server.com/doc/CometQL/Star.Comet-Chat/usersAuth.php?id=".((int)$user_id)."&hash=".getUserHash($user_id)."\n"; 
        var_dump($output);
        echo "</pre>";
    }
}

// Call when a user leaves the site
function sendUserExitInfo($user_id)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://comet-server.com/doc/CometQL/Star.Comet-Chat/usersAuth.php?id=".((int)$user_id));
    curl_setopt($ch, CURLOPT_TIMEOUT, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $output = curl_exec($ch);
    curl_close($ch);
    if($output !="usersAuth-ok")
    {
        echo "<pre>Error accessing api:sendUserExitInfo:\n";
        var_dump($output);
        echo "</pre>";
    }
}


