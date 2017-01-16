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
include './config.php';
include './common.php';

$user_id = getUserIdOrDie(); 
 
$contacts = array();
$contacts[] = $user_id; 

$contacts_type = array();
$resInfo = array();
$selfInfo = false;

$qery = "select contacts.*, new_messages.count_new_msg as newMessages
        from 
            ( 
                SELECT users_relations.to_user_id, users_relations.type as relation_type, last_msg_time.time as last_message_time  
                FROM `users_relations` 
                    left join (SELECT from_user_id, time FROM `messages` where to_user_id = ".$user_id." group by from_user_id) as last_msg_time  
                    on users_relations.to_user_id = last_msg_time.from_user_id 
                where users_relations.user_id = ".$user_id."
            ) as contacts
            left join
            (
                SELECT from_user_id, count(*) as count_new_msg FROM `messages` where read_time = 0 and  to_user_id = ".$user_id." group by from_user_id
            ) as new_messages  
            on contacts.to_user_id = new_messages.from_user_id";

$result = mysqli_query(StarCometChat::conf()->getDB(), $qery);



if(mysqli_errno(StarCometChat::conf()->getDB()) != 0)
{
    echo "Error code:".mysqli_errno(StarCometChat::conf()->getDB())." ".mysqli_error(StarCometChat::conf()->getDB())."";
}
else if(mysqli_num_rows($result))
{
    $countContacts = 0;
    while($row = mysqli_fetch_assoc($result))
    {
        if($countContacts < 240)
        {
            // Ограничение не позволяющие запросить статусы online/offline больше чем у 240 последних контактов
            $contacts[] = $row['to_user_id']; 
            $countContacts++;
        }
        
        $contacts_type[$row['to_user_id']] = $row;
    }  
} 

$info = getUsersInfo($contacts);
if($info === false || $info === NULL)
{
    echo json_encode(array("success"=>false, "error" => "У вас нет права пользоватся чатом, обратитесь к администратору"));
    exit();
}

// Получение статусов пользователей с комет сервера  
$result = mysqli_query(StarCometChat::conf()->getComet(), "SELECT id, time FROM users_time WHERE id IN( ".join(",", $contacts).");"); 
while($row = mysqli_fetch_assoc($result))
{
    $contacts_type[$row['id']]['last_online_time'] =  $row['time'];
}

foreach ($info as $key => &$value)
{ 
    if($user_id == $value['user_id'])
    {
        $selfInfo = array_merge($value, $contacts_type[$value['user_id']]); 
        continue;
    }
    $resInfo[$value['user_id']] = array_merge($value, $contacts_type[$value['user_id']]); 
}

if(!is_array($selfInfo))
{  
    echo json_encode(array("success"=>false, "error" => "У вас нет права пользоватся чатом"));
    exit(); 
}

$selfInfo["is_admin"] = in_array($user_id, StarCometChat::conf()->getAdminIds());  
$selfInfo['login'] = preg_replace("/^.*?([^\/]*)$/usi", "$1", $selfInfo['login']);
if(!isset($selfInfo['error']))
{
    $selfInfo['error'] = "";
}

// mysqli_query(StarCometChat::conf()->getDB(), "REPLACE into  `users` (`id`, `login`, `avatar_url`, `error` )VALUES('".  mysqli_real_escape_string(StarCometChat::conf()->getDB(), $selfInfo['user_id'])."', '".  mysqli_real_escape_string(StarCometChat::conf()->getDB(), $selfInfo['login'])."', '".  mysqli_real_escape_string(StarCometChat::conf()->getDB(), $selfInfo['avatar_url'])."', '".  mysqli_real_escape_string(StarCometChat::conf()->getDB(), $selfInfo['error'])."')");
 

echo json_encode(array("success"=>true, "contacts" => $resInfo, "myInfo" => $selfInfo));









