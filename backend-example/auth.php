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
<h1>Authorization was successful <?php echo $userId; ?></h1>
List of chat users with whom you can send a message:<br>
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
            echo $i.' - <b>'.$info['name'].'</b> (you)'; 
        }
        else
        { 
            echo $i.' - <a href="https://comet-server.com/doc/CometQL/Star.Comet-Chat/backend-example/userPage.php?name='.$info['login'].'">'.$info['name'].'</a>'; 
        }
        echo "<br>"; 
    }while(true); 
?></body>
</html>