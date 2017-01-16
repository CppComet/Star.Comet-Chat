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
include './config.php';
   
$userId = (int)$_GET['id'];
$_SESSION['userId'] = $userId;
sendUserLoginInfo($userId);

?><!DOCTYPE html>
<html lang="ru" dir="ltr" class="no-js">
<head>
    <meta charset="utf-8" />
</head>
<body>
<h1>Авторизация прошла успешно <?php echo $userId; ?></h1>
Список пользователей чата которым вы можете отправить сообщение:<br>
<?php  
    $i=0;
    do{
        $i++;
        $info = getUserInfoById($i);
        if($info == null)
        {
            break;
        }
        
        if($userId == $info['user_id'])
        {
            echo $i.' - <b>'.$info['name'].'</b> (вы)'; 
        }
        else
        { 
            echo $i.' - <a href="https://comet-server.com/doc/CometQL/Star.Comet-Chat/backend-example/userPage.php?name='.$info['login'].'">'.$info['name'].'</a>'; 
        }
        echo "<br>"; 
    }while(true); 
?></body>
</html>