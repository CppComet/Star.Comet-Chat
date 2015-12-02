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

ini_set('display_errors','on');
error_reporting(E_ALL);

/**
 * ip адрес с которого разрешено вызывать api методы управления чатом
 * Или false если ограничение отключено (не безопасно)
 */
$trusted_ip = false;

session_start();

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


/**
 * Функция возвращает массив с информацией о пользователе по его идентификатору
 * Обычно информация о пользователях хранится в бд, но здесь для примера в целях упрощения кода информация захардкодена в виде массива
 */
function getUserInfoById($id)
{
    $users[] = array();
    $users[] = array("user_id" => 1, "avatar_url" => "http://comet-server.ru/doc/CometQL/Star.Comet-Chat/img/avatar0.png", "name" => "Виктор", "city" => "Москва", "age" => 24, "status" => "active", "login" => "lodin1", "error" => "", "status" => false);
    $users[] = array("user_id" => 2, "avatar_url" => "http://comet-server.ru/doc/CometQL/Star.Comet-Chat/img/avatar0.png", "name" => "Лена", "city" => "Владивосток", "age" => 24, "status" => "active", "login" => "lodin2");
    $users[] = array("user_id" => 3, "avatar_url" => "http://comet-server.ru/doc/CometQL/Star.Comet-Chat/img/avata.png", "name" => "Маша", "city" => "Владивосток", "age" => 0.9, "status" => "active", "login" => "lodin3");
    $users[] = array("user_id" => 4, "avatar_url" => "http://comet-server.ru/doc/CometQL/Star.Comet-Chat/img/avata2.png", "name" => "Михаил", "city" => "Москва", "age" => 30, "status" => "active", "login" => "lodin4");
    $users[] = array("user_id" => 5, "avatar_url" => "http://comet-server.ru/doc/CometQL/Star.Comet-Chat/img/avatar0.png", "name" => "Иван", "city" => "Хабаровск", "age" => 20, "status" => "active", "login" => "lodin5");
    $users[] = array("user_id" => 6, "avatar_url" => "http://comet-server.ru/doc/CometQL/Star.Comet-Chat/img/avatar0.png", "name" => "Артём", "city" => "Артём", "age" => 12, "status" => "active", "login" => "lodin6");

    if(isset($users[(int)$id]))
    {
        return $users[(int)$id];
    }

    return null;
}

/**
 * Функция возвращает массив с информацией о пользователе по его лигину
 * Обычно информация о пользователях хранится в бд, но здесь для примера в целях упрощения кода информация захардкодена в виде массива
 */
function getUserInfoByLogin($login)
{
    $id=0;
    do{
        $id++;
        $info = getUserInfoById($id);
        if($info == null)
        {
            return null;
        }
        else if($info['login'] == $login)
        {
            return $info;
        }
        
    }while(true); 
}

/**
 * Возвращает строку хеша авторизации для пользователя по его идентификатору 
 */
function getUserHash($user_id)
{
    $salt = $user_id."bgDf5gfDF4VD5bBFg7f8".date("z").getUserInfoById($user_id)['age'].getUserInfoById($user_id)['city'].getUserInfoById($user_id)['login'];
    return md5($salt.md5($salt));
}

// Вызвать при входе пользователя на сайт
function sendUserLoginInfo($user_id)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://comet-server.ru/doc/CometQL/Star.Comet-Chat/usersAuth.php?id=".((int)$user_id)."&hash=".getUserHash($user_id));
    curl_setopt($ch, CURLOPT_TIMEOUT, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
    $output = curl_exec($ch);
    curl_close($ch);
    
    if($output !="usersAuth-ok")
    {
        echo "<pre>Ошибка доступа к api:sendUserLoginInfo:\n";
        var_dump($output);
        echo "</pre>";
    }
}

// Вызвать при выходе пользователя с сайта
function sendUserExitInfo($user_id)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://comet-server.ru/doc/CometQL/Star.Comet-Chat/usersAuth.php?id=".((int)$user_id));
    curl_setopt($ch, CURLOPT_TIMEOUT, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
    $output = curl_exec($ch);
    curl_close($ch);
    if($output !="usersAuth-ok")
    {
        echo "<pre>Ошибка доступа к api:sendUserExitInfo:\n";
        var_dump($output);
        echo "</pre>";
    }
}


