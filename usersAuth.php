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
include './common.php';

testIpOrDie();

if(isset($_GET['hash']))
{
    // User Login
    mysqli_query(StarCometChat::conf()->getComet(), "INSERT INTO users_auth (id, hash)VALUES (".((int)$_GET['id']).", '".mysqli_real_escape_string(StarCometChat::conf()->getComet(),$_GET['hash'])."')");  
}
else
{
    // User Logout
    mysqli_query(StarCometChat::conf()->getComet(), "DELETE FROM users_auth WHERE id = ".((int)$_GET['id'])); 
}
echo "usersAuth-ok";
 








