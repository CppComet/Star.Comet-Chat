<?php
include './config.php';
include './common.php';
$user_id = getUserIdOrDie();

$abuse_to_user_id = (int)$_POST['abuse_to'];
if($abuse_to_user_id <= 0)
{
    die("Не верный параметр user_id_to");
}
    
// Определяем тип связи пользователей (избранное забанен нейтральная)
$result = mysqli_query(app::conf()->getDB(),
        "INSERT INTO `abuse` (`id`, `user_id_from`, `user_id_to`, `time`) VALUES (NULL, '".$user_id."', '".$abuse_to_user_id."', '".date("U")."');");
 
$abuse_id = mysqli_insert_id(app::conf()->getDB());
$msg = array("abuse_id" => $abuse_id, "user_id_from" => $user_id, "user_id_to"=>$abuse_to_user_id, "time" => date("U"));

$result = mysqli_query(app::conf()->getDB(),"SELECT id, login, avatar_url FROM `users` where id in(".$abuse_to_user_id.",".$user_id.")"); 
$row = mysqli_fetch_assoc($result);
if($row['id'] == $user_id)
{
    $msg["loginFrom"] = $row['login'];
    $msg["avatarFrom"] = $row['avatar_url'];
}
else
{
    $msg["loginTo"] = $row['login'];
    $msg["avatarTo"] = $row['avatar_url'];
}

$row = mysqli_fetch_assoc($result);
if($row['id'] == $user_id)
{
    $msg["loginFrom"] = $row['login'];
    $msg["avatarFrom"] = $row['avatar_url'];
}
else
{
    $msg["loginTo"] = $row['login'];
    $msg["avatarTo"] = $row['avatar_url'];
}



sendMsgToAdmin("newAbuseForUser", $msg);
echo json_encode(array("abuse_id" => $abuse_id));
