<?php
/**
 * Apache License 2.0
 * @author Trapenok Victor (Трапенок Виктор Викторович), Levhav@ya.ru, 89244269357
 * Буду рад новым заказам на разработку чего ни будь.
 *
 * Levhav@ya.ru
 * Skype:Levhav
 * 89244269357
 * 
 * https://github.com/Levhav/Star.Comet-Chat
 */


/**
 * На адрес отправляется POST запрос и в параметре users список идентификаторов пользователей информация о которых нужна
 * Ответ в json
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



