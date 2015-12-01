<?php
include './config.php';
   
$userId = (int)$_GET['id'];
$_SESSION['userId'] = $userId;
sendUserLoginInfo($userId);

?>
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
            echo $i.' - <a href="http://comet-server.ru/doc/CometQL/Star.Comet-Chat/backend-example/userPage.php?name='.$info['login'].'">'.$info['name'].'</a>'; 
        }
        echo "<br>"; 
    }while(true); 
?>