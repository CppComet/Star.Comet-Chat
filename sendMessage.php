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
 * Отправляет сообщение
 */

include './config.php';
include './common.php';
$user_id = getUserIdOrDie();

$message = htmlspecialchars($_POST['message']);
if(empty($message) && !isset($_FILES["img"]))
{
    die("Не верный параметр message");
}

$to_user_id = (int)$_POST['user_id_to'];
if($to_user_id <= 0)
{
    die("Не верный параметр user_id_to");
}

if($user_id == $to_user_id)
{
    die("Не надо писать самому себе");
}
  
if(isset($_FILES["img"]))
{
    if($_FILES["img"]['size'] > getConfArray('max_img_size'))
    {
        die("Не правильный размер ".$_FILES["img"]['size']);
    }

    $fileName = $user_id."_".$to_user_id."_".floor(microtime(true)*1000);
    if($_FILES["img"]['type'] == "image/jpeg" )
    {
        $fileName.=".jpeg";
    }
    else if( $_FILES["img"]['type'] == "image/png")
    {
        $fileName.=".png";
    }
    else
    {
        die("Не правильный тип ".$_FILES["img"]['type']);
    }


    if (!move_uploaded_file($_FILES["img"]['tmp_name'], $_SERVER['DOCUMENT_ROOT']."/".getConfArray('file_dir')."/".$fileName))
    {
        die("Ошибка от move_uploaded_file");
    }
    
    $message = "[[img=".$fileName."]]" . $message;
}

/**
 * тип связи пользователей (избранное забанен нейтральная)
 */
$relation_type = 0;

/**
 * Если не false то это первое сообщение в диалоге и переменная хранит информацию о пользователе
 */ 
$NewContactInfo = false;

// Определяем тип связи пользователей (избранное забанен нейтральная)
$result = mysqli_query(StarCometChat::conf()->getDB(),
        "SELECT type FROM `users_relations` where (user_id = ".$to_user_id." and to_user_id = ".$user_id.") limit 1 ");
if(!mysqli_num_rows($result))
{
    // Если связи нет то добавляем её
    mysqli_query(StarCometChat::conf()->getDB(),"INSERT INTO `users_relations` (`user_id`, `to_user_id`, `type`) VALUES ('".$user_id."', '".$to_user_id."', '0'),  ('".$to_user_id."', '".$user_id."', '0');"); 
    $NewContactInfo = getUsersInfo(array($user_id, $to_user_id));
    
    $NewContactInfo[0]['login'] = preg_replace("/^.*?([^\/]*)$/usi", "$1", $NewContactInfo[0]['login']);    
    // Добавляем запись в таблицу пользователей для сохранения связки id -> логина
    mysqli_query(StarCometChat::conf()->getDB(),"INSERT INTO `users` (`id`, `login`, `avatar_url`) VALUES ('".$NewContactInfo[0]['user_id']."', '".mysqli_real_escape_string(StarCometChat::conf()->getDB(),$NewContactInfo[0]['login'])."', '".mysqli_real_escape_string(StarCometChat::conf()->getDB(),$NewContactInfo[0]['avatar_url'])."');");
    
    
    if($user_id != $to_user_id)
    {
        $NewContactInfo[1]['login'] = preg_replace("/^.*?([^\/]*)$/usi", "$1", $NewContactInfo[1]['login']);
        // Добавляем запись в таблицу пользователей для сохранения связки id -> логина
        mysqli_query(StarCometChat::conf()->getDB(),
                "INSERT INTO `users` (`id`, `login`, `avatar_url`) VALUES ('".$NewContactInfo[1]['user_id']."', '".mysqli_real_escape_string(StarCometChat::conf()->getDB(),$NewContactInfo[1]['login'])."', '".mysqli_real_escape_string(StarCometChat::conf()->getDB(),$NewContactInfo[1]['avatar_url'])."');");
    }
    if($NewContactInfo[0]['user_id'] == $user_id)
    {
        $NewContactInfo= $NewContactInfo[0];
    }
    else
    {
        $NewContactInfo= $NewContactInfo[1];
    }
}
else
{
    $row = mysqli_fetch_assoc($result);
    $relation_type = $row['type'];
}

$userAgent = "";
if(isset($_SERVER['HTTP_USER_AGENT']))
{
    $userAgent = mysqli_real_escape_string(StarCometChat::conf()->getDB(), $_SERVER['HTTP_USER_AGENT']);
}

// Запись в бд
mysqli_query(StarCometChat::conf()->getDB(), "INSERT INTO `messages` (`id`, `from_user_id`, `to_user_id`, `time`, `read_time`, `message`, `userAgent`)"
        . " VALUES (NULL, '".$user_id."', '".$to_user_id."', '".date("U")."', '0', '".mysqli_real_escape_string(StarCometChat::conf()->getDB(), $message)."', '".$userAgent."')");
 

$msg_id = mysqli_insert_id(StarCometChat::conf()->getDB());
$msg = array("id" => $msg_id, "message" => base64_encode($message), "from_user_id" => $user_id, "relation_type"=>$relation_type, "new_contact" => $NewContactInfo);
echo json_encode(array("msg_id" => $msg_id, "message_text" => $message));


$result = mysqli_query(StarCometChat::conf()->getDB(),"SELECT id, login, avatar_url FROM `users` where id in(".$to_user_id.",".$user_id.")"); 
$row = mysqli_fetch_assoc($result);
if($row['id'] == $user_id)
{
    $msg["loginFrom"] = $row['login'];
    $msg["avatarFrom"] = $row['avatar_url'];
}
else
{
    $msg["loginTo"] = $row['login'];
    $msg["avatarTo"] = $row['avatar_url'];
}

$row = mysqli_fetch_assoc($result);
if($row['id'] == $user_id)
{
    $msg["loginFrom"] = $row['login'];
    $msg["avatarFrom"] = $row['avatar_url'];
}
else
{
    $msg["loginTo"] = $row['login'];
    $msg["avatarTo"] = $row['avatar_url'];
}

$msg['user_id'] = $user_id;

// Отправка пользователю
$q = mysqli_real_escape_string(StarCometChat::conf()->getComet(), json_encode($msg));
mysqli_query(StarCometChat::conf()->getComet(), "INSERT INTO users_messages (id, event, message)VALUES (".$to_user_id.", 'newMessage', '".$q."')");
 

// Отправка админам 
sendMsgToAdmin("newMessageForUser", $msg);

