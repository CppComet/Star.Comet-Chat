<?php
/**
 * Помечает сообщение прочитанным
 */

include './config.php';
include './common.php';
$user_id = getUserIdOrDie();

markAsReadMessageArray((int)$_POST['from_user_id'], $user_id);
echo json_encode(array("success"=>true));
  