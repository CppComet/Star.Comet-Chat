<?php
 include './config.php';

 $userName = preg_replace("#[^0-9A-z]#u", "", $_GET['name']);
 $userId = 1;
?>
<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Подключаем библиотеки -->
    <script src="http://comet-server.ru/CometServerApi.js" type="text/javascript"></script>
    <script src="http://comet-server.ru/doc/CometQL/Star.Comet-Chat/js/jquery.min.js"      type="text/javascript"></script>
    <script src="http://comet-server.ru/doc/CometQL/Star.Comet-Chat/js/jquery.cookie.js"   type="text/javascript" ></script>
    <title>Чат пользователь - <?php echo $userName; ?></title>
</head>
<body>
    <h1>Страница пользователя <?php echo $userName; ?></h1>
    <button onclick="StarCometChat.openDialog(<? echo $userId; ?>);">Написать этому пользователю</button>
    <div id="newMsgIndicator"  onclick="StarCometChat.openDialog();"></div>
<script>
$(document).ready(function()
{
    StarCometChat.init({
        user_id: 7820,
        user_key: '71601b8f7a7565130f70711c34583d1d', 
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
</body>
</html>