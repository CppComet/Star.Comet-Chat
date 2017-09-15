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
 
?>
<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Connect Libraries -->
    <script src="https://comet-server.com/CometServerApi.js" type="text/javascript"></script>
    <script src="https://comet-server.com/doc/CometQL/Star.Comet-Chat/js/jquery.min.js"      type="text/javascript"></script>
    <script src="https://comet-server.com/doc/CometQL/Star.Comet-Chat/js/jquery.cookie.js"   type="text/javascript" ></script>
    <title>Chat</title>
</head>
<body>
    <h1>Chat description</h1>
    
    <a href="https://comet-server.com/wiki/doku.php/comet:star-comet-chat">Chat description</a>
    <br>
    <a href="https://github.com/Levhav/Star.Comet-Chat">Chat source codes</a>
    
    
    <h1>Log in to chat on behalf of</h1>
<?php 
    $i=0;
    do{
        $i++;
        $info = getUserInfoById($i);
        if($info == null)
        {
            break;
        }
        
        echo $i.' - <a href="https://comet-server.com/doc/CometQL/Star.Comet-Chat/backend-example/auth.php?id='.($i).'">'.$info['name'].'</a>'; 
        echo "<br>"; 
    }while(true); 
?> 
</body>
</html>