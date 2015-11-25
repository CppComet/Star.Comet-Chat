<?php
/**
 * На адрес отправляется POST запрос и в параметре users список идентификаторов пользователей информация о которых мне нужна
 * Ответ в json
 */ 
$uArr = split(",", $_POST["users"]);

$users = array();

$users[] = array();


$users[] = array("user_id" => 1, "avatar_url" => "http://test9.lan/img/avatar0.png", "name" => "Виктор", "city" => "Москва", "age" => 24, "status" => "active", "lodin" => "lodin1");
$users[] = array("user_id" => 2, "avatar_url" => "http://test9.lan/img/avatar0.png", "name" => "Лена", "city" => "Владивосток", "age" => 24, "status" => "active", "lodin" => "lodin2");
$users[] = array("user_id" => 3, "avatar_url" => "http://test9.lan/img/avata.png", "name" => "Маша", "city" => "Владивосток", "age" => 0.9, "status" => "active", "lodin" => "lodin3"); 
$users[] = array("user_id" => 4, "avatar_url" => "http://test9.lan/img/avata2.png", "name" => "Михаил", "city" => "Москва", "age" => 30, "status" => "active", "lodin" => "lodin4"); 
$users[] = array("user_id" => 5, "avatar_url" => "http://test9.lan/img/avatar0.png", "name" => "Иван", "city" => "Хабаровск", "age" => 20, "status" => "active", "lodin" => "lodin5"); 
$users[] = array("user_id" => 6, "avatar_url" => "http://test9.lan/img/avatar0.png", "name" => "Артём", "city" => "Артём", "age" => 12, "status" => "active", "lodin" => "lodin6"); 
for($i=7; $i< 307; $i++)
{ 
    $users[] = array("user_id" => $i, "avatar_url" => "http://test9.lan/img/avatar0.png", "name" => "Артём".$i, "city" => "Москва".$i, "age" => $i, "status" => "active", "lodin" => "lodiniii".$i); 
} 
$res = array();
foreach ($uArr as $key => $value)
{
    if(isset($users[(int)$value]))
    {
        $res[] = $users[(int)$value];
    }
}

echo json_encode($res);



