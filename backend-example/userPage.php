<?php
 include './config.php';

 $userInfo = getUserInfoByLogin(preg_replace("#[^0-9A-z]#u", "", $_GET['login'])); 
 if($userInfo == null)
 {
     die("Пользователь ".preg_replace("#[^0-9A-z]#u", "", $_GET['login'])." не существует");
 }
?>
<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Подключаем библиотеки -->
    <script src="http://comet-server.ru/CometServerApi.js" type="text/javascript"></script>  
    
    <script type="text/javascript" src="http://comet-server.ru/doc/CometQL/Star.Comet-Chat/js/jquery.min.js"></script> 
    <script type="text/javascript" src="http://comet-server.ru/doc/CometQL/Star.Comet-Chat/js/jquery.cookie.js"></script>
    <script type="text/javascript" src="http://comet-server.ru/doc/CometQL/Star.Comet-Chat/js/moment.min.js"></script>
    <script type="text/javascript" src="http://comet-server.ru/doc/CometQL/Star.Comet-Chat/tinyscrollbar/jquery.tinyscrollbar.js"></script>
    
    <script type="text/javascript" src="http://comet-server.ru/doc/CometQL/Star.Comet-Chat/chat.js"></script>
    <link rel="stylesheet" href="http://comet-server.ru/doc/CometQL/Star.Comet-Chat/chat.css"> 



    <title>Чат пользователь - <?php echo $userInfo['name']; ?></title>
</head>
<body>
    <h1>Страница пользователя <?php echo $userInfo['name']; ?></h1>
    <button onclick="StarCometChat.openDialog(<?php echo $userInfo['user_id']; ?>);">Написать этому пользователю</button>
    <div id="newMsgIndicator"  onclick="StarCometChat.openDialog();"></div>
<script>
  
var user_id = <?php echo $_SESSION['userId']; ?>;  
var user_key = "<?php echo getUserHash($_SESSION['userId']); ?>";  
        
$(document).ready(function()
{
    StarCometChat.init({
        user_id: user_id,
        user_key: user_key, 
        open:false,
        success:function()
        {
            var c = StarCometChat.countNewMessagesSum();
            if(c > 0)
            {
                $('#newMsgIndicator').html("У вас "+ c + " новых сообщений");
            }
        }
    });
});

</script>
<br>
<img src="<?php echo $userInfo['avatar_url']; ?>"><br>
<pre><?php var_dump($userInfo); ?></pre>
</body>
</html>