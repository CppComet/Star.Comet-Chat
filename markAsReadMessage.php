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
 * Помечает сообщение прочитанным
 */

include './config.php';
include './common.php';
$user_id = getUserIdOrDie();

markAsReadMessageArray((int)$_POST['from_user_id'], $user_id);
echo json_encode(array("success"=>true));
  