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

$user_id = getUserIdOrDie(); 

$result = mysqli_query(StarCometChat::conf()->getDB(), "SELECT count(*) FROM `messages` where to_user_id = ".$user_id." and read_time = 0");
$row = mysqli_fetch_assoc($result);
echo json_encode(array("count_message"=>$row));