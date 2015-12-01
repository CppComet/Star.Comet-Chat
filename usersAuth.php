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
include './common.php';

testIpOrDie();

if(isset($_GET['hash']))
{
    // Вход пользователя
    mysqli_query(app::conf()->getComet(), "INSERT INTO users_auth (id, hash)VALUES (".((int)$_GET['id']).", '".mysqli_real_escape_string(app::conf()->getComet(),$_GET['hash'])."')");  
}
else
{
    // Выход пользователя 
    mysqli_query(app::conf()->getComet(), "DELETE FROM users_auth WHERE id = ".((int)$_GET['id'])); 
}
echo "usersAuth-ok";
 








