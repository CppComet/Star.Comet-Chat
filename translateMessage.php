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
 * 
 */

/**
 * Файл перевода сообщений
 * 
 * Список поддерживаемых языков
 * https://tech.yandex.ru/translate/doc/dg/concepts/langs-docpage/
 */
 
include './config.php';
include './common.php';


$user_id = getUserIdOrDie();

$message_id = (int)$_POST['message_id']; 
$language = $_POST['language']; 
if(strlen($language) != 2 && !preg_match("#^[a-z][a-z]$#", $language))
{
    die("Не верные данные запроса");
}
 
$result = mysqli_query(StarCometChat::conf()->getDB(), "SELECT * FROM `messages` where id = ".$message_id." and ( from_user_id= ".$user_id." or to_user_id = ".$user_id." ) ");
if(mysqli_errno(StarCometChat::conf()->getDB()) != 0)
{
    die("Error code:".mysqli_errno(StarCometChat::conf()->getDB())." ".mysqli_error(StarCometChat::conf()->getDB())."");
}
else if(!mysqli_num_rows($result))
{
    die("Нет доступа");
}

$msg = mysqli_fetch_assoc($result);

$result = mysqli_query(StarCometChat::conf()->getDB(), "SELECT text, language FROM `messages_translate` where message_id = ".$message_id." and language = '".$language."' ");
if(mysqli_errno(StarCometChat::conf()->getDB()) != 0)
{
    die("Error code:".mysqli_errno(StarCometChat::conf()->getDB())." ".mysqli_error(StarCometChat::conf()->getDB())."");
}
else if(mysqli_num_rows($result))
{ 
    $msg = mysqli_fetch_assoc($result);
    echo json_encode(array("text" => $msg['text'], "language" => $msg['language'], "translate" => false));
    exit();
}


$messageText = preg_replace("/\[\[img=([A-z0-9\._]+)\]\]/usi", "",  $msg['message']);

 
$translate = @file_get_contents("https://translate.yandex.net/api/v1.5/tr.json/translate?key=".getConfArray("yandex_translate_key")."&text=".urlencode($messageText)."&lang=".$language."&options=1");
if(!$translate)
{
    die("Ошибка перевода");
}

$data = json_decode($translate, true);

mysqli_query(StarCometChat::conf()->getDB(), "INSERT INTO `messages_translate` (`id`, `message_id`, `language`, `text`)"
        . " VALUES (NULL, '".$message_id."', '".$language."', '".  mysqli_escape_string(StarCometChat::conf()->getDB(), $data['text'][0])."')");
          
echo json_encode(array("text" => $data['text'][0], "language" => $data['detected']['lang']));
