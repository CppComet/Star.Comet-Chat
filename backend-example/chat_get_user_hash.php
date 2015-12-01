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
 * На адрес отправляется POST запрос и в параметре id список идентификаторов пользователей hash которых нужен
 * Ответ в json
 */ 

include './config.php';
testIpOrDie();

echo getUserHash((int)$_POST["id"]);



