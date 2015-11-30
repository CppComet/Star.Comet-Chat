<?php

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


$result = mysqli_query(app::conf()->getDB(), "SELECT * FROM `users` WHERE error = '' and id = ".$user_id_from);
if(!mysqli_num_rows($result))
{
    die("Доступ запрещён. error not empty");
}

/**
 * Если не false то это первое сообщение в диалоге и переменная хранит информацию о пользователе
 */ 
$NewContactInfo = false;

$result = mysqli_query(app::conf()->getDB(), "SELECT id, from_user_id, to_user_id, read_time, time*1000 as time, message FROM `messages` where (from_user_id = ".$user_id_from." and to_user_id = ".$user_id_to.") or (to_user_id = ".$user_id_from." and from_user_id = ".$user_id_to.") order by time desc limit ".($page*app::conf()->page_size).", ".app::conf()->page_size);
if(mysqli_errno(app::conf()->getDB()) != 0)
{
    echo "Error code:".mysqli_errno(app::conf()->getDB())." ".mysqli_error(app::conf()->getDB())."";
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
$result = mysqli_query(app::conf()->getComet(), "SELECT time FROM users_time WHERE id = ".$user_id_to.""); 
$row = mysqli_fetch_assoc($result);


echo json_encode(array("success"=>true, "history" => $messages, "last_online_time" => $row['time'], "new_contact" => $NewContactInfo));









































