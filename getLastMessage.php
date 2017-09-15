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
 
include './config.php';
include './common.php';
$user_id_from = getUserIdOrDie();

$user_id_to = (int)$_POST['user_id_to']; 

if(isset($_POST['page']))
{
    $page = (int)$_POST['page'];
}
else
{ 
    $page = 0;
}
 
$messages = array();
 
/**
 * Если не false то это первое сообщение в диалоге и переменная хранит информацию о пользователе
 */ 
$NewContactInfo = false;

$result = mysqli_query(StarCometChat::conf()->getDB(), "SELECT id, from_user_id, to_user_id, read_time, time*1000 as time, message FROM `messages` where (from_user_id = ".$user_id_from." and to_user_id = ".$user_id_to.") or (to_user_id = ".$user_id_from." and from_user_id = ".$user_id_to.") order by time desc limit ".($page*StarCometChat::conf()->page_size).", ".StarCometChat::conf()->page_size);
if(mysqli_errno(StarCometChat::conf()->getDB()) != 0)
{
    echo "Error code:".mysqli_errno(StarCometChat::conf()->getDB())." ".mysqli_error(StarCometChat::conf()->getDB())."";
}
else if(mysqli_num_rows($result))
{
    while($row = mysqli_fetch_assoc($result))
    {
        $messages[] = $row;
    }
    
    markAsReadMessageArray($user_id_to, $user_id_from);
}
else
{ 
    // Человек ещё не в списке контактов так как в базе нет не одного сообщения
    $NewContactInfo = @getUsersInfo($user_id_to)[0];
}

// Получение статусов пользователей с комет сервера  
$result = mysqli_query(StarCometChat::conf()->getComet(), "SELECT time FROM users_time WHERE id = ".$user_id_to.""); 
$row = mysqli_fetch_assoc($result);


echo json_encode(array("success"=>true, "history" => $messages, "last_online_time" => $row['time'], "new_contact" => $NewContactInfo));









































