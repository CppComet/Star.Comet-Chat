<?php

include './config.php';
include './common.php';

$user_id = getUserIdOrDie(); 

$result = mysqli_query(app::conf()->getDB(), "SELECT count(*) FROM `messages` where to_user_id = ".$user_id." and read_time = 0");
$row = mysqli_fetch_assoc($result);
echo json_encode(array("count_message"=>$row));