<?php 
include './config.php';
 
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
    <title>Чат</title>
</head>
<body>
    <h1>Описание чата</h1>
    
    <a href="http://comet-server.ru/wiki/doku.php/comet:star-comet-chat">Описание чата</a>
    <br>
    <a href="https://github.com/Levhav/Star.Comet-Chat">Исходные коды чата</a>
    
    
    <h1>Войти в чат от имени</h1>
<?php 
    $i=0;
    do{
        $i++;
        $info = getUserInfoById($i);
        if($info == null)
        {
            break;
        }
        
        echo $i.' - <a href="http://comet-server.ru/doc/CometQL/Star.Comet-Chat/backend-example/auth.php?id='.($i).'">'.$info['name'].'</a>'; 
        echo "<br>"; 
    }while(true); 
?> 
</body>
</html>