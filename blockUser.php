<?php
include './config.php';
include './common.php';
$user_id = getUserIdOrDie();

$block_user_id = (int)$_POST['block_user_id'];
if($block_user_id <= 0)
{
    die("Не верный параметр block_user_id");
}


$block = (int)$_POST['block'];
if($block < 0 || $block > 2)
{
    die("Не верный параметр block");
}

if($block == 2 && in_array($block_user_id, app::conf()->admin_ids))
{
    echo json_encode(array("success" => false, "error" => "Нельзя заблокировать администратора"));
    exit();
}
     
mysqli_query(app::conf()->getDB(),"UPDATE `users_relations` SET `type` = '".$block."' WHERE `user_id` = ".$user_id." AND `to_user_id` = ".$block_user_id);

echo json_encode(array("success" => true));