<?php
/**
 * На адрес отправляется POST запрос и в параметре users список идентификаторов пользователей информация о которых нужна
 * Ответ в json
 */ 

include './config.php';
testIpOrDie();
 
$uArr = split(",", $_POST["users"]);

$users = array();
 
$res = array();
foreach ($uArr as $key => $value)
{
    $info = getUserInfoById($value);
    if($info != null)
    {
        $res[] = $info;
    }
}

echo json_encode($res);



