<?php
/**
 * На адрес отправляется POST запрос и в параметре id список идентификаторов пользователей hash которых нужен
 * Ответ в json
 */ 

include './config.php';
testIpOrDie();

echo getUserHash((int)$_POST["id"]);



