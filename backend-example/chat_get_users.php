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


/**
 * The address is sent to the POST request and in the users parameter the list of user IDs whose information is needed
 * Answer in json
 */ 

include './config.php';
testIpOrDie();
 
$uArr = explode(",", $_POST["users"]);

$users = array();
 
$res = array();
foreach ($uArr as $key => $value)
{
    $info = getUserInfoById($value);
    if($info != null)
    {
        $res[] = $info;
    }
}

echo json_encode($res);



