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

 $userInfo = getUserInfoByLogin(preg_replace("#[^0-9A-z]#u", "", $_GET['name'])); 
 if($userInfo == null)
 {
     die("Пользователь ".preg_replace("#[^0-9A-z]#u", "", $_GET['name'])." не существует");
 }
?>
<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Подключаем библиотеки -->
    <script src="https://comet-server.com/CometServerApi.js" type="text/javascript"></script>  
    
    <script type="text/javascript" src="https://comet-server.com/doc/CometQL/Star.Comet-Chat/js/jquery.min.js"></script> 
    <script type="text/javascript" src="https://comet-server.com/doc/CometQL/Star.Comet-Chat/js/jquery.cookie.js"></script>
    <script type="text/javascript" src="https://comet-server.com/doc/CometQL/Star.Comet-Chat/js/moment.min.js"></script>
    <script type="text/javascript" src="https://comet-server.com/doc/CometQL/Star.Comet-Chat/tinyscrollbar/jquery.tinyscrollbar.js"></script>
    
    <script type="text/javascript" src="https://comet-server.com/doc/CometQL/Star.Comet-Chat/chat.js"></script>
    <link rel="stylesheet" href="https://comet-server.com/doc/CometQL/Star.Comet-Chat/chat.css"> 



    <title>Чат пользователь - <?php echo $userInfo['name']; ?></title>
</head>
<body>
<style>
pre{
    word-wrap: break-word;
}
</style>
    <h1>Страница пользователя <?php echo $userInfo['name']; ?></h1>
    <button onclick="StarCometChat.openDialog(<?php echo $userInfo['user_id']; ?>);">Написать этому пользователю</button>
    <div id="newMsgIndicator"  onclick="StarCometChat.openDialog();"></div>
<script>
  
var user_id = <?php echo $_SESSION['userId']; ?>;  
var user_key = "<?php echo getUserHash($_SESSION['userId']); ?>";  
        
$(document).ready(function()
{
    
    /**
     * Подключение к комет серверу. Для возможности принимать команды.
     * dev_id ваш публичный идентифиукатор разработчика
     * user_id идентификатор пользователя под которым вы вошли.
     * user_key ваш хеш авторизации.
     */
    CometServer().start({dev_id:15, user_id:user_id, user_key: user_key})
     
    /**
     * Инициализируем модуль чата, происходит инициализация и загрузка данных необходимых для работы.
     * Но окно чата этим вызовом не открывается.
     */
    StarCometChat.init({
        user_id: user_id,
        user_key: user_key, 
        open:false,
        
        // Параметр home_dir содержит адрес расположения php скриптов чата
        home_dir: "//comet-server.com/doc/CometQL/Star.Comet-Chat",

        // Параметр home_dir содержит адрес расположения php скриптов чата 
        user_url_tpl:"//comet-server.com/user/",
        user_avatar_url_tpl: "",
        // Настройка списка выриантов перевода сообщений
        langs: [{name:'ru', textName:['Русский']}, {name:'en', textName:['Английский']}, {name:'uk', textName:['Украинский']}],
            
        // Функция назначенная в success вызывается после успешной инициализации чата.
        success:function()
        {
            // Вызов countNewMessagesSum возвращает количество новых сообщений. Работает корректно только после завершения инициализации чата
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
