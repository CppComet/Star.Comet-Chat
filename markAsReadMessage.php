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
 * Marks a message with a read
 */

include './config.php';
include './common.php';
$user_id = getUserIdOrDie();

markAsReadMessageArray((int)$_POST['from_user_id'], $user_id);
echo json_encode(array("success"=>true));
  