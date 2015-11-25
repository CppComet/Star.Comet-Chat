<?php


// Вызвать при входе пользователя на сайт
function sendUserLoginInfo($user_id)
{ 
    $ch = curl_init(); 
    curl_setopt($ch, CURLOPT_URL, "http://chat.lovelama.ru/chat/usersAuth.php?id=".((int)$user_id)."&hash=".session_id()); 
    curl_setopt($ch, CURLOPT_TIMEOUT, 1);
    $output = curl_exec($ch); 
    curl_close($ch); 
    print $output; 
}

// Вызвать при выходе пользователя с сайта
function sendUserExitInfo($user_id)
{ 
    $ch = curl_init(); 
    curl_setopt($ch, CURLOPT_URL, "http://chat.lovelama.ru/chat/usersAuth.php?id=".((int)$user_id)); 
    curl_setopt($ch, CURLOPT_TIMEOUT, 1);
    $output = curl_exec($ch); 
    curl_close($ch); 
    print $output;  
}















