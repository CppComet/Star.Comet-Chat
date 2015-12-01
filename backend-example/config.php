<?php

$trusted_ip = false;


/**
 * Разрешает доступ только для запросов с доверенных ip адресов
 */
function testIpOrDie()
{
    global $trusted_ip;
    if ( $trusted_ip != false && $_SERVER['HTTP_X_REAL_IP'] != getConfArray("trusted_ip"))
    {
        die("Нет доступа с ip ".$_SERVER['HTTP_X_REAL_IP']);
    }
}


function getUserInfoById($id)
{ 
    $users[] = array();  
    $users[] = array("user_id" => 1, "avatar_url" => "http://test9.lan/img/avatar0.png", "name" => "Виктор", "city" => "Москва", "age" => 24, "status" => "active", "lodin" => "lodin1", "error" => "", "status" => false);
    $users[] = array("user_id" => 2, "avatar_url" => "http://test9.lan/img/avatar0.png", "name" => "Лена", "city" => "Владивосток", "age" => 24, "status" => "active", "lodin" => "lodin2");
    $users[] = array("user_id" => 3, "avatar_url" => "http://test9.lan/img/avata.png", "name" => "Маша", "city" => "Владивосток", "age" => 0.9, "status" => "active", "lodin" => "lodin3"); 
    $users[] = array("user_id" => 4, "avatar_url" => "http://test9.lan/img/avata2.png", "name" => "Михаил", "city" => "Москва", "age" => 30, "status" => "active", "lodin" => "lodin4"); 
    $users[] = array("user_id" => 5, "avatar_url" => "http://test9.lan/img/avatar0.png", "name" => "Иван", "city" => "Хабаровск", "age" => 20, "status" => "active", "lodin" => "lodin5"); 
    $users[] = array("user_id" => 6, "avatar_url" => "http://test9.lan/img/avatar0.png", "name" => "Артём", "city" => "Артём", "age" => 12, "status" => "active", "lodin" => "lodin6"); 

    if(isset($users[(int)$id]))
    {
        return $users[(int)$id];
    }
    
    return null;
}


function getUserHash($user_id)
{  
    $salt = $user_id."bgDf5gfDF4VD5bBFg7f8".date("z").getUserInfoById($user_id)['age'].getUserInfoById($user_id)['city'].getUserInfoById($user_id)['lodin'];       // Соль для генерации хешей авторизации
    return md5($salt.md5($salt));
}

// Вызвать при входе пользователя на сайт
function sendUserLoginInfo($user_id)
{ 
    $ch = curl_init(); 
    curl_setopt($ch, CURLOPT_URL, "http://comet-server.ru/doc/CometQL/Star.Comet-Chat/usersAuth.php?id=".((int)$user_id)."&hash=".getUserHash($user_id)); 
    curl_setopt($ch, CURLOPT_TIMEOUT, 1);
    $output = curl_exec($ch); 
    curl_close($ch); 
    print $output; 
}

// Вызвать при выходе пользователя с сайта
function sendUserExitInfo($user_id)
{ 
    $ch = curl_init(); 
    curl_setopt($ch, CURLOPT_URL, "http://comet-server.ru/doc/CometQL/Star.Comet-Chat/usersAuth.php?id=".((int)$user_id)); 
    curl_setopt($ch, CURLOPT_TIMEOUT, 1);
    $output = curl_exec($ch); 
    curl_close($ch); 
    print $output;  
}


